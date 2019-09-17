<?php

namespace DataCue\MagentoModule;

use Magento\Framework\App\ObjectManager;

class Website
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private static $connection;

    /**
     * @var string
     */
    private static $tableName;

    public static function isSingleStoreMode()
    {
        $store = ObjectManager::getInstance()->get('Magento\Framework\Store');
        return $store->isSingleStoreMode();
    }

    public static function getSingleStoreApiKeyAndApiSecret()
    {
        $collectionFactory = ObjectManager::getInstance()->get('Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory');
        $apiKeyItems = $collectionFactory->create()->addFieldToFilter('path', 'datacue/api_key')->getColumnValues('value');
        $apiSecretItems = $collectionFactory->create()->addFieldToFilter('path', 'datacue/api_secret')->getColumnValues('value');

        return [
            count($apiKeyItems) > 0 ? $apiKeyItems[0] : null,
            count($apiSecretItems) > 0 ? $apiSecretItems[0] : null,
        ];
    }

    public static function getWebsiteList()
    {
        static::init('store_website');

        return static::$connection->fetchAll("
            SELECT website_id, name FROM `" . static::$tableName . "` WHERE `website_id` > 0");
    }

    public static function getActiveWebsiteIds()
    {
        static::init();

        $res = static::$connection->fetchAll("SELECT website_id FROM `" . static::$tableName . "`");

        if (empty($res)) {
            return [];
        }

        return array_map(function ($item) {
            return intval($item['website_id']);
        }, $res);
    }

    public static function getApiKeyAndApiSecretByWebsiteId($websiteId)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);

        return static::$connection->fetchRow("
            SELECT api_key, api_secret FROM `" . static::$tableName . "` WHERE `website_id` = $websiteId");
    }

    public static function setApiKeyAndApiSecretByWebsiteId($websiteId, $apiKey, $apiSecret)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);
        $apiKey = static::$connection->quote($apiKey);
        $apiSecret = static::$connection->quote($apiSecret);

        $existing = static::$connection->fetchRow("
            SELECT 1 FROM `" . static::$tableName . "` WHERE `website_id` = $websiteId");

        if ($existing) {
            return static::$connection->query("
                UPDATE `" . static::$tableName . "` SET `api_key` = $apiKey, `api_secret` = $apiSecret , `last_updated_at` = NOW() WHERE `website_id` = $websiteId
            ")->rowCount() > 0;
        } else {
            return static::$connection->query("
                INSERT INTO `" . static::$tableName . "` (`website_id`, `api_key`, `api_secret`, `last_updated_at`) 
                VALUES ($websiteId, $apiKey, $apiSecret, NOW())")->rowCount() > 0;
        }
    }

    public static function deleteApiKeyAndApiSecretByWebsiteId($websiteId)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);

        return static::$connection->query("DELETE FROM `" . static::$tableName . "` WHERE `website_id` = $websiteId")->rowCount();
    }

    private static function init($name = 'datacue_clients')
    {
        $objectManager = ObjectManager::getInstance();
        /**
         * @var $resource \Magento\Framework\App\ResourceConnection
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        static::$connection = $resource->getConnection();
        static::$tableName = $resource->getTableName($name);
    }
}
