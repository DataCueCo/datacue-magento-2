<?php

namespace DataCue\MagentoModule\Common;

use DataCue\MagentoModule\Queue;
use DataCue\MagentoModule\Website;

/**
 * Initializer
 */
class Initializer
{
    /**
     * chunk size of each package
     */
    const CHUNK_SIZE = 200;

    /**
     * @var \Magento\Framework\App\ResourceConnection $resource
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    private $connection;

    /**
     * @var \DataCue\Client $datacueClient
     */
    private $datacueClient;

    /**
     * @var int $websiteId
     */
    private $websiteId;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \DataCue\Client $datacueClient,
        $websiteId
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->datacueClient = $datacueClient;
        $this->websiteId = $websiteId;
    }

    public function check()
    {
        $this->datacueClient->overview->all();
    }

    public function init()
    {
        if (!Queue::isActionExistingByWebsiteId('init', $this->websiteId)) {
            $this->initProducts();
            $this->initCategories();
            $this->initUsers();
            $this->initOrders();
        }
    }

    public function initProducts($type = 'init')
    {
        $table = $this->resource->getTableName('catalog_product_website');
        $products = $this->connection->fetchAll("SELECT `product_id` FROM `" . $table . "` WHERE `website_id` = {$this->websiteId}");
        $productIds = array_map(function ($item) {
            return $item['product_id'];
        }, $products);

        if ($type === 'init') {
            $res = $this->datacueClient->overview->products();
            $existingIds = !is_null($res->getData()->ids) ? $res->getData()->ids : [];
            $productIdsList = array_chunk(array_diff($productIds, $existingIds), static::CHUNK_SIZE);
        } else {
            $productIdsList = array_chunk($productIds, static::CHUNK_SIZE);
        }

        foreach($productIdsList as $item) {
            $job = ['ids' => $item];
            Queue::addJobWithoutModelId($type, 'products', $job, $this->websiteId);
        }
    }

    public function initCategories($type = 'init')
    {
        $table = $this->resource->getTableName('catalog_category_entity');
        $categories = $this->connection->fetchAll("SELECT `entity_id` FROM `" . $table . "` WHERE `parent_id` > 1");
        $categoryIds = array_map(function ($item) {
            return $item['entity_id'];
        }, $categories);

        if ($type === 'init') {
            $res = $this->datacueClient->overview->categories();
            $existingIds = !is_null($res->getData()->ids) ? $res->getData()->ids : [];
            $categoryIdsList = array_chunk(array_diff($categoryIds, $existingIds), static::CHUNK_SIZE);
        } else {
            $categoryIdsList = array_chunk($categoryIds, static::CHUNK_SIZE);
        }

        foreach($categoryIdsList as $item) {
            $job = ['ids' => $item];
            Queue::addJobWithoutModelId($type, 'categories', $job, $this->websiteId);
        }
    }

    public function initUsers($type = 'init')
    {
        $table = $this->resource->getTableName('customer_entity');
        $users = $this->connection->fetchAll("SELECT `entity_id` FROM `" . $table . "` WHERE `website_id` = {$this->websiteId}");
        $userIds = array_map(function ($item) {
            return $item['entity_id'];
        }, $users);

        if ($type === 'init') {
            $res = $this->datacueClient->overview->users();
            $existingIds = !is_null($res->getData()->ids) ? $res->getData()->ids : [];
            $userIdsList = array_chunk(array_diff($userIds, $existingIds), static::CHUNK_SIZE);
        } else {
            $userIdsList = array_chunk($userIds, static::CHUNK_SIZE);
        }

        foreach($userIdsList as $item) {
            $job = ['ids' => $item];
            Queue::addJobWithoutModelId($type, 'users', $job, $this->websiteId);
        }
    }

    public function initOrders($type = 'init')
    {
        $table = $this->resource->getTableName('store');
        $store = $this->connection->fetchRow("SELECT `store_id` FROM `" . $table . "` WHERE `website_id` = {$this->websiteId}");
        if (!$store) {
            return;
        }

        $table = $this->resource->getTableName('sales_order');
        $orders = $this->connection->fetchAll("SELECT `entity_id` FROM `" . $table . "` WHERE `store_id` = {$store['store_id']}");
        $orderIds = array_map(function ($item) {
            return $item['entity_id'];
        }, $orders);

        if ($type === 'init') {
            $res = $this->datacueClient->overview->orders();
            $existingIds = !is_null($res->getData()->ids) ? $res->getData()->ids : [];
            $orderIdsList = array_chunk(array_diff($orderIds, $existingIds), static::CHUNK_SIZE);
        } else {
            $orderIdsList = array_chunk($orderIds, static::CHUNK_SIZE);
        }

        foreach($orderIdsList as $item) {
            $job = ['ids' => $item];
            Queue::addJobWithoutModelId($type, 'orders', $job, $this->websiteId);
        }
    }
}
