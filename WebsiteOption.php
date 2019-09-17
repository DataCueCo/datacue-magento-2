<?php

namespace DataCue\MagentoModule;

use Magento\Framework\App\ObjectManager;

class WebsiteOption
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private static $connection;

    /**
     * @var string
     */
    private static $tableName;

    public static function getOptionsByWebsiteId($websiteId)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);

        $data = static::$connection->fetchAll("SELECT `key`, `value` FROM `" . static::$tableName . "` WHERE `website_id` = $websiteId");

        if (empty($data)) {
            return [];
        }

        $res = [];
        foreach ($data as $item) {
            $res[$item['key']] = $item['value'];
        }

        return $res;
    }

    public static function getOptionByWebsiteIdAndKey($websiteId, $key)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);
        $key = static::$connection->quote($key);

        $res = static::$connection->fetchRow("SELECT `value` FROM `" . static::$tableName . "` WHERE `website_id` = $websiteId AND `key` = $key");

        if (empty($res)) {
            return null;
        }

        return $res['value'];
    }

    public static function setOption($websiteId, $key, $value)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);
        $key = static::$connection->quote($key);
        $value = static::$connection->quote($value);

        $existing = static::$connection->fetchRow("SELECT 1 FROM `" . static::$tableName . "` WHERE `website_id` = $websiteId AND `key` = $key");

        if (!$existing) {
            return static::$connection->query("
                INSERT INTO `" . static::$tableName . "` (`website_id`, `key`, `value`, `last_updated_at`) 
                VALUES ($websiteId, $key, $value, NOW())")->rowCount() > 0;
        }

        return static::$connection->query("
            UPDATE `" . static::$tableName . "` SET `key` = $key, `value` = $value , `last_updated_at` = NOW() WHERE `website_id` = $websiteId AND `key` = $key
        ")->rowCount() > 0;
    }

    private static function init($name = 'datacue_client_options')
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
