<?php

namespace DataCue\MagentoModule\Common;

use DataCue\MagentoModule\Queue;

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

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \DataCue\Client $datacueClient
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->datacueClient = $datacueClient;
    }

    public function init()
    {
        if (!Queue::isActionExisting('init')) {
            $this->initProducts();
            $this->initUsers();
            $this->initOrders();
        }
    }

    private function initProducts()
    {
        $table = $this->resource->getTableName('catalog_product_entity');
        $products = $this->connection->fetchAll("SELECT `entity_id` FROM `" . $table . "`");
        $productIds = array_map(function ($item) {
            return $item['entity_id'];
        }, $products);

        $res = $this->datacueClient->overview->products();
        $existingIds = !is_null($res->getData()->ids) ? $res->getData()->ids : [];

        $productIdsList = array_chunk(array_diff($productIds, $existingIds), static::CHUNK_SIZE);

        foreach($productIdsList as $item) {
            $job = ['ids' => $item];
            Queue::addJobWithoutModelId('init', 'products', $job);
        }
    }

    private function initUsers()
    {
        $table = $this->resource->getTableName('customer_entity');
        $users = $this->connection->fetchAll("SELECT `entity_id` FROM `" . $table . "`");
        $userIds = array_map(function ($item) {
            return $item['entity_id'];
        }, $users);

        $res = $this->datacueClient->overview->users();
        $existingIds = !is_null($res->getData()->ids) ? $res->getData()->ids : [];

        $userIdsList = array_chunk(array_diff($userIds, $existingIds), static::CHUNK_SIZE);

        foreach($userIdsList as $item) {
            $job = ['ids' => $item];
            Queue::addJobWithoutModelId('init', 'users', $job);
        }
    }

    private function initOrders()
    {
        $table = $this->resource->getTableName('sales_order');
        $orders = $this->connection->fetchAll("SELECT `entity_id` FROM `" . $table . "` WHERE `state` != 'canceled'");
        $orderIds = array_map(function ($item) {
            return $item['entity_id'];
        }, $orders);

        $res = $this->datacueClient->overview->orders();
        $existingIds = !is_null($res->getData()->ids) ? $res->getData()->ids : [];

        $orderIdsList = array_chunk(array_diff($orderIds, $existingIds), static::CHUNK_SIZE);

        foreach($orderIdsList as $item) {
            $job = ['ids' => $item];
            Queue::addJobWithoutModelId('init', 'orders', $job);
        }
    }
}
