<?php

namespace DataCue\MagentoModule\Common;

use DataCue\MagentoModule\Modules\Category;
use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Website;
use DataCue\MagentoModule\Utils\Log;
use DataCue\MagentoModule\Modules\Product;
use DataCue\MagentoModule\Modules\User;
use DataCue\MagentoModule\Modules\Order;
use DataCue\Client;

/**
 * Schedule
 */
class Schedule
{
    /**
     * Interval between two cron job.
     */
    const INTERVAL = 20;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    private $configWriter;

    /**
     * @var \DataCue\Client $client
     */
    private $client;

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configWriter = $configWriter;
    }

    public function start()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/last_cron_job_time')->getColumnValues('value');
        $now = time();

        if (count($items) === 0 || $now - intval($items[0]) >= static::INTERVAL) {
            $this->configWriter->save('datacue/last_cron_job_time', strval($now));
            $this->runCronJob();
        }
    }

    private function runCronJob()
    {
        // get job
        $job = Queue::getNextAliveJob();
        if (empty($job)) {
            return;
        }
        $credentials = Website::getApiKeyAndApiSecretByWebsiteId($job['website_id']);
        if (empty($credentials)) {
            return;
        }

        // create datacue client
        $this->client = new Client(
            $credentials['api_key'],
            $credentials['api_secret'],
            ['max_try_times' => 3],
            file_exists(__DIR__ . '/../staging') ? 'development' : 'production'
        );

        Log::info('runCronJob');
        Queue::startJob($job['id']);

        try {
            if ($job['action'] === 'init' || $job['action'] === 'reinit') {
                $this->doInit($job['model'], $job['job']);
            } else {
                switch ($job['model']) {
                    case 'products':
                        $this->doProductsJob($job['action'], $job['job']);
                        break;
                    case 'variants':
                        $this->doProductsJob($job['action'], $job['job']);
                        break;
                    case 'categories':
                        $this->doCategoriesJob($job['action'], $job['job']);
                        break;
                    case 'users':
                        $this->doUsersJob($job['action'], $job['job']);
                        break;
                    case 'guest_users':
                        $this->doUsersJob($job['action'], $job['job']);
                        break;
                    case 'orders':
                        $this->doOrdersJob($job['action'], $job['job']);
                        break;
                    case 'events':
                        $this->doEventJob($job['action'], $job['job']);
                        break;
                    default:
                        break;
                }
            }
            Queue::updateJobStatus($job['id'], Queue::STATUS_SUCCESS);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            Queue::updateJobStatus($job['id'], Queue::STATUS_FAILURE);
        }
    }

    /**
     * Initialize data
     *
     * @param $model
     * @param $job
     * @throws \DataCue\Exceptions\RetryCountReachedException
     * @throws \DataCue\Exceptions\ClientException
     * @throws \DataCue\Exceptions\ExceedBodySizeLimitationException
     * @throws \DataCue\Exceptions\ExceedListDataSizeLimitationException
     * @throws \DataCue\Exceptions\InvalidEnvironmentException
     * @throws \DataCue\Exceptions\NetworkErrorException
     * @throws \DataCue\Exceptions\UnauthorizedException
     */
    private function doInit($model, $job)
    {
        if ($model === 'products') {
            // batch create products
            $data = [];
            foreach ($job->ids as $id) {
                $product = Product::getProductById($id);
                $parentProduct = Product::getParentProduct($id);
                if (is_null($parentProduct)) {
                    $variantIds = Product::getVariantIds($product->getId());
                    if (count($variantIds) === 0) {
                        $data[] = Product::buildProductForDataCue($product, true);
                    } else {
                        foreach ($variantIds as $vId) {
                            $variant = Product::getProductById($vId);
                            if ($variant) {
                                $data[] = Product::buildVariantForDataCue($product, $variant, true);
                            }
                        }
                    }
                }
            }
            $res = $this->client->products->batchCreate($data);
            Log::info('batch create products response: ' . $res);
        } elseif ($model === 'categories') {
            // batch create categories
            $data = [];
            foreach ($job->ids as $id) {
                $category = Category::getCategoryById($id);
                if ($category->getParentId() > 1) {
                    $data[] = Category::buildCategoryForDataCue($category, true);
                }
            }
            $res = $this->client->categories->batchCreate($data);
            Log::info('batch create categories response: ' . $res);
        } elseif ($model === 'users') {
            // batch create users
            $data = [];
            foreach ($job->ids as $id) {
                $user = User::getUserById($id);
                $data[] = User::buildUserForDataCue($user, true);
            }
            $res = $this->client->users->batchCreate($data);
            Log::info('batch create users response: ' . $res);
        } elseif ($model === 'orders') {
            // batch create orders
            $guestData = [];
            $orderData = [];
            foreach ($job->ids as $id) {
                $order = Order::getOrderById($id);
                if (Order::isOrderValid($order)) {
                    if (is_null($order->getCustomerId())) {
                        $existing = false;
                        foreach ($guestData as $guest) {
                            if ($guest['user_id'] === $order->getCustomerEmail()) {
                                $existing = true;
                                break;
                            }
                        }
                        if (!$existing) {
                            $guestData[] = Order::buildGuestUserForDataCue($order);
                        }
                    }
                    $orderData[] = Order::buildOrderForDataCue($order, true);
                }
            }
            if (count($guestData) > 0) {
                $res = $this->client->users->batchCreate($guestData);
                Log::info('batch create guest users response: ' . $res);
            }
            Log::info($orderData);
            $res = $this->client->orders->batchCreate($orderData);
            Log::info('batch create orders response: ' . $res);
        }
    }

    /**
     * Do products job
     *
     * @param $action
     * @param $job
     * @throws \DataCue\Exceptions\RetryCountReachedException
     * @throws \DataCue\Exceptions\ClientException
     * @throws \DataCue\Exceptions\ExceedBodySizeLimitationException
     * @throws \DataCue\Exceptions\ExceedListDataSizeLimitationException
     * @throws \DataCue\Exceptions\InvalidEnvironmentException
     * @throws \DataCue\Exceptions\NetworkErrorException
     * @throws \DataCue\Exceptions\UnauthorizedException
     */
    private function doProductsJob($action, $job)
    {
        switch ($action) {
            case 'create':
                $res = $this->client->products->create($job->item);
                Log::info('create product response: ' . $res);
                break;
            case 'update':
                $res = $this->client->products->update($job->productId, $job->variantId, $job->item);
                Log::info('update product response: ' . $res);
                break;
            case 'delete':
                $res = $this->client->products->delete($job->productId, $job->variantId);
                Log::info('delete product response: ' . $res);
                break;
            case 'delete_all':
                $res = $this->client->products->deleteAll();
                Log::info('delete all products response: ' . $res);
                break;
            default:
                break;
        }
    }

    private function doCategoriesJob($action, $job)
    {
        switch ($action) {
            case 'create':
                $res = $this->client->categories->create($job->item);
                Log::info('create category response: ' . $res);
                break;
            case 'update':
                $res = $this->client->categories->update($job->categoryId, $job->item);
                Log::info('update category response: ' . $res);
                break;
            case 'delete':
                $res = $this->client->categories->delete($job->categoryId);
                Log::info('delete category response: ' . $res);
                break;
            case 'delete_all':
                $res = $this->client->categories->deleteAll();
                Log::info('delete all categories response: ' . $res);
                break;
            default:
                break;
        }
    }

    /**
     * Do users job
     *
     * @param $action
     * @param $job
     * @throws \DataCue\Exceptions\RetryCountReachedException
     * @throws \DataCue\Exceptions\ClientException
     * @throws \DataCue\Exceptions\ExceedBodySizeLimitationException
     * @throws \DataCue\Exceptions\ExceedListDataSizeLimitationException
     * @throws \DataCue\Exceptions\InvalidEnvironmentException
     * @throws \DataCue\Exceptions\NetworkErrorException
     * @throws \DataCue\Exceptions\UnauthorizedException
     */
    private function doUsersJob($action, $job)
    {
        switch ($action) {
            case 'create':
                $res = $this->client->users->create($job->item);
                Log::info('create user response: ' . $res);
                break;
            case 'update':
                $res = $this->client->users->update($job->userId, $job->item);
                Log::info('update user response: ' . $res);
                break;
            case 'delete':
                $res = $this->client->users->delete($job->userId);
                Log::info('delete user response: ' . $res);
                break;
            case 'delete_all':
                $res = $this->client->users->deleteAll();
                Log::info('delete all users response: ' . $res);
                break;
            default:
                break;
        }
    }

    /**
     * Do orders job
     *
     * @param $action
     * @param $job
     * @throws \DataCue\Exceptions\RetryCountReachedException
     * @throws \DataCue\Exceptions\ClientException
     * @throws \DataCue\Exceptions\ExceedBodySizeLimitationException
     * @throws \DataCue\Exceptions\ExceedListDataSizeLimitationException
     * @throws \DataCue\Exceptions\InvalidEnvironmentException
     * @throws \DataCue\Exceptions\NetworkErrorException
     * @throws \DataCue\Exceptions\UnauthorizedException
     */
    private function doOrdersJob($action, $job)
    {
        switch ($action) {
            case 'create':
                $res = $this->client->orders->create($job->item);
                Log::info('create order response: ', $res);
                break;
            case 'cancel':
                $res = $this->client->orders->cancel($job->orderId);
                Log::info('cancel order response: ', $res);
                break;
            case 'delete':
                $res = $this->client->orders->delete($job->orderId);
                Log::info('delete order response: ', $res);
                break;
            case 'delete_all':
                $res = $this->client->orders->deleteAll();
                Log::info('delete all orders response: ' . $res);
                break;
            default:
                break;
        }
    }

    /**
     * Do event job
     * 
     * @param $action
     * @param $job
     * @throws \DataCue\Exceptions\ClientException
     * @throws \DataCue\Exceptions\ExceedBodySizeLimitationException
     * @throws \DataCue\Exceptions\ExceedListDataSizeLimitationException
     * @throws \DataCue\Exceptions\InvalidEnvironmentException
     * @throws \DataCue\Exceptions\NetworkErrorException
     * @throws \DataCue\Exceptions\RetryCountReachedException
     * @throws \DataCue\Exceptions\UnauthorizedException
     */
    private function doEventJob($action, $job)
    {
        switch ($action) {
            case 'track':
                $res = $this->client->events->track($job->user, $job->event);
                Log::info('track event response: ', $res);
                break;
            default:
                break;
        }
    }
}
