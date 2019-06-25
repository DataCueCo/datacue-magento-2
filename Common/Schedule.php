<?php

namespace DataCue\MagentoModule\Common;

use DataCue\MagentoModule\Queue;
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
        Log::info('runCronJob');
        // create datacue client
        $this->client = new Client(
            $this->getApiKey(),
            $this->getApiSecret(),
            ['max_try_times' => 3],
            file_exists(__DIR__ . '/../staging') ? 'development' : 'production'
        );

        // get job
        $job = Queue::getNextAliveJob();
        Queue::startJob($job['id']);

        try {
            if ($job['action'] === 'init') {
                $this->doInit($job['model'], $job['job']);
            } else {
                switch ($job['model']) {
                    case 'products':
                        $this->doProductsJob($job['action'], $job['job']);
                        break;
                    case 'variants':
                        $this->doProductsJob($job['action'], $job['job']);
                        break;
                    case 'users':
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

    private function getApiKey()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_key')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
    }

    private function getApiSecret()
    {
        $collection = $this->collectionFactory->create();
        $items = $collection->addFieldToFilter('path', 'datacue/api_secret')->getColumnValues('value');

        return count($items) > 0 ? $items[0] : '';
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
        global $wpdb;
        if ($model === 'products') {
            // batch create products
            $data = [];
            foreach ($job->ids as $id) {
                $product = Product::getProductById($id);
                $parentProduct = Product::getParentProduct($id);
                if (is_null($parentProduct)) {
                    $data[] = Product::buildProductForDataCue($product, true);
                } else {
                    $data[] = Product::buildVariantForDataCue($parentProduct, $product, true);
                }
            }
            $res = $this->client->products->batchCreate($data);
            Log::info('batch create products response: ' . $res);
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
            $data = [];
            foreach ($job->ids as $id) {
                $order = Order::getOrderById($id);
                $data[] = Order::buildOrderForDataCue($order, true);
            }
            $res = $this->client->orders->batchCreate($data);
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
                Log::info('create variant response: ' . $res);
                break;
            case 'update':
                $res = $this->client->products->update($job->productId, $job->variantId, $job->item);
                Log::info('update product response: ' . $res);
                break;
            case 'delete':
                $res = $this->client->products->delete($job->productId, $job->variantId);
                Log::info('delete variant response: ' . $res);
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
