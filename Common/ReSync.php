<?php

namespace DataCue\MagentoModule\Common;

use DataCue\Client;
use DataCue\MagentoModule\Website;
use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Utils\Log;
use Magento\Framework\App\ObjectManager;
use DataCue\MagentoModule\Modules\Order;
use DataCue\MagentoModule\Modules\User;
use DataCue\MagentoModule\Modules\Product;

class Resync
{
    /**
     * Interval between two cron job.
     */
    const INTERVAL = 900;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    private $configWriter;

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configWriter = $configWriter;
    }

    public function execute()
    {
        if (!$this->checkIfExecute()) {
            return;
        }

        $ids = Website::getActiveWebsiteIds();

        foreach ($ids as $id) {
            Log::info('begin re-sync, website_id = ' . $id);

            $credentials = Website::getApiKeyAndApiSecretByWebsiteId($id);

            // create datacue client
            $client = new Client(
                $credentials['api_key'],
                $credentials['api_secret'],
                ['max_try_times' => 3],
                file_exists(__DIR__ . '/../staging') ? 'development' : 'production'
            );

            try {
                $res = $client->client->sync();
                Log::info('get resync info: ' . $res);
                $data = $res->getData();
                if (property_exists($data, 'users')) {
                    $this->executeUsers($client, $id, $data->users);
                }
                if (property_exists($data, 'products')) {
                    $this->executeProducts($client, $id, $data->products);
                }
                if (property_exists($data, 'orders')) {
                    $this->executeOrders($client, $id, $data->orders);
                }
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
        }
    }

    private function executeUsers($client, $websiteId, $data)
    {
        if (is_null($data)) {
            return;
        }

        if ($data === 'full') {
            Queue::addJobWithoutModelId('delete_all', 'users', [], $websiteId);
            $this->getInitializer($client, $websiteId)->initUsers('reinit');
        } elseif (is_array($data)) {
            foreach ($data as $userId) {
                Queue::addJob('delete', 'users', $userId, ['userId' => $userId], $websiteId);
                $user = User::getUserById($userId);
                if (empty($user) || empty($user->getId())) {
                    continue;
                }
                Queue::addJob(
                    'update',
                    'users',
                    $user->getId(),
                    [
                        'userId' => $user->getId(),
                        'item' => User::buildUserForDataCue($user, false),
                    ],
                    $websiteId
                );
            }
        }
    }

    private function executeProducts($client, $websiteId, $data)
    {
        if (is_null($data)) {
            return;
        }

        if ($data === 'full') {
            Queue::addJobWithoutModelId('delete_all', 'products', [], $websiteId);
            $this->getInitializer($client, $websiteId)->initProducts('reinit');
        } elseif (is_array($data)) {
            foreach ($data as $productId) {
                Queue::addJob('delete', 'products', $productId, ['productId' => $productId, 'variantId' => null], $websiteId);
                $product = Product::getProductById($productId);
                if (empty($product) || empty($product->getId())) {
                    continue;
                }
                if ($product->getTypeId() === 'simple') {
                    Queue::addJob(
                        'create',
                        'products',
                        $product->getId(),
                        [
                            'productId' => $product->getId(),
                            'variantId' => 'no-variants',
                            'item' => Product::buildProductForDataCue($product, true),
                        ],
                        $websiteId
                    );
                } elseif ($product->getTypeId() === 'configurable') {
                    $variantIds = Product::getVariantIds($product->getId());
                    foreach ($variantIds as $variantId) {
                        $variant = Product::getProductById($variantId);
                        Queue::addJob(
                            'create',
                            'variants',
                            $variant->getId(),
                            [
                                'productId' => $product->getId(),
                                'variantId' => $variant->getId(),
                                'item' => Product::buildVariantForDataCue($product, $variant, true),
                            ],
                            $websiteId
                        );
                    }
                }
            }
        }
    }

    private function executeOrders($client, $websiteId, $data)
    {
        if (is_null($data)) {
            return;
        }

        if ($data === 'full') {
            Queue::addJobWithoutModelId('delete_all', 'orders', [], $websiteId);
            $this->getInitializer($client, $websiteId)->initOrders('reinit');
        } elseif (is_array($data)) {
            foreach ($data as $orderId) {
                $order = Order::getOrderById($orderId);
                if (empty($order) || empty($order->getId())) {
                    continue;
                }
                if (is_null($order->getCustomerId())) {
                    Queue::addJob(
                        'create',
                        'guest_users',
                        $order->getId(),
                        [
                            'item' => Order::buildGuestUserForDataCue($order),
                        ],
                        $websiteId
                    );
                }
                Queue::addJob(
                    'create',
                    'orders',
                    $order->getId(),
                    [
                        'item' => Order::buildOrderForDataCue($order, true),
                    ],
                    $websiteId
                );
            }
        }
    }

    /**
     * @return \DataCue\MagentoModule\Common\Initializer $initializer
     */
    private function getInitializer($client, $websiteId)
    {
        return new Initializer(ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection'), $client, $websiteId);
    }

    private function checkIfExecute()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/last_resync_time')->getColumnValues('value');
        $now = time();

        if (count($items) === 0 || $now - intval($items[0]) >= static::INTERVAL) {
            $this->configWriter->save('datacue/last_resync_time', strval($now));
            return true;
        }

        return false;
    }
}