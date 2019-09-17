<?php

namespace DataCue\MagentoModule;

use Magento\Framework\App\ObjectManager;

class Queue
{
    /**
     * job status list
     */
    const STATUS_NONE = 0;
    const STATUS_PENDING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILURE = 3;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private static $connection;

    /**
     * @var string
     */
    private static $tableName;

    private static function init()
    {
        $objectManager = ObjectManager::getInstance();
        /**
         * @var $resource \Magento\Framework\App\ResourceConnection
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        static::$connection = $resource->getConnection();
        static::$tableName = $resource->getTableName('datacue_queue');
    }

    public static function addJob($action, $model, $modelId, $job, $websiteId)
    {
        if (!in_array($websiteId, Website::getActiveWebsiteIds())) {
            return;
        }

        static::init();

        $action = static::$connection->quote($action);
        $model = static::$connection->quote($model);
        $modelId = static::$connection->quote("$modelId");
        $job = static::$connection->quote(json_encode($job));
        $websiteId = static::$connection->quote($websiteId);

        return static::$connection->query("
			INSERT INTO `" . static::$tableName . "` (`website_id`, `action`, `model`, `model_id`, `job`, `status`, `created_at`) 
			VALUES ($websiteId, $action, $model, $modelId, $job, 0, NOW())")->rowCount() > 0;
    }

    public static function addJobWithoutModelId($action, $model, $job, $websiteId)
    {
        if (!in_array($websiteId, Website::getActiveWebsiteIds())) {
            return;
        }

        static::init();

        $action = static::$connection->quote($action);
        $model = static::$connection->quote($model);
        $job = static::$connection->quote(json_encode($job));
        $websiteId = static::$connection->quote($websiteId);

        return static::$connection->query("
			INSERT INTO `" . static::$tableName . "` (`website_id`, `action`, `model`, `job`, `status`, `created_at`) 
			VALUES ($websiteId, $action, $model, $job, 0, NOW())")->rowCount() > 0;
    }

    public static function updateJob($id, $job)
    {
        static::init();

        $id = static::$connection->quote("$id");
        $job = static::$connection->quote(json_encode($job));

        return static::$connection->query("
            UPDATE `" . static::$tableName . "` SET `job` = $job WHERE `id` = $id
        ")->rowCount() > 0;
    }

    public static function isJobExisting($action, $model, $modelId)
    {
        static::init();

        $action = static::$connection->quote($action);
        $model = static::$connection->quote($model);
        $modelId = static::$connection->quote("$modelId");

        return static::$connection->fetchOne("
            SELECT 1 FROM `" . static::$tableName . "`
            WHERE `action` = $action AND `model` = $model AND `model_id` = $modelId") === '1';
    }

    public static function isActionExisting($action)
    {
        static::init();

        $action = static::$connection->quote($action);

        return static::$connection->fetchOne("
            SELECT 1 FROM `" . static::$tableName . "` WHERE `action` = $action") === '1';
    }

    public static function isActionExistingByWebsiteId($action, $websiteId)
    {
        static::init();

        $action = static::$connection->quote($action);
        $websiteId = static::$connection->quote($websiteId);

        return static::$connection->fetchOne("
            SELECT 1 FROM `" . static::$tableName . "` WHERE `action` = $action AND `website_id` = $websiteId") === '1';
    }

    public static function getAliveJob($action, $model, $modelId)
    {
        static::init();

        $action = static::$connection->quote($action);
        $model = static::$connection->quote($model);
        $modelId = static::$connection->quote("$modelId");

        $job = static::$connection->fetchRow("
            SELECT * FROM `" . static::$tableName . "`
            WHERE `action` = $action AND `model` = $model AND `model_id` = $modelId AND `status` = 0");
        if ($job) {
            $job['job'] = json_decode($job['job']);
        }
        return $job;
    }

    public static function getNextAliveJob()
    {
        static::init();

        $job = static::$connection->fetchRow("
            SELECT * FROM `" . static::$tableName . "` WHERE `status` = 0");
        if ($job) {
            $job['job'] = json_decode($job['job']);
        }
        return $job;
    }

    public static function getAllInitJob()
    {
        static::init();

        $jobs = static::$connection->fetchAll("
            SELECT * FROM `" . static::$tableName . "` WHERE `action` = 'init'");
        foreach ($jobs as &$job) {
            $job['job'] = json_decode($job['job']);
        }
        return $jobs;
    }

    public static function getAllInitJobByWebsiteId($websiteId)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);

        $jobs = static::$connection->fetchAll("
            SELECT * FROM `" . static::$tableName . "` WHERE `action` = 'init' AND `website_id` = $websiteId");
        foreach ($jobs as &$job) {
            $job['job'] = json_decode($job['job']);
        }
        return $jobs;
    }

    public static function startJob($id)
    {
        static::init();

        $id = static::$connection->quote("$id");

        return static::$connection->query("
            UPDATE `" . static::$tableName . "` SET `status` = " . static::STATUS_PENDING . ", `executed_at` = NOW() WHERE `id` = $id
        ")->rowCount() > 0;
    }

    public static function updateJobStatus($id, $status)
    {
        static::init();

        $id = static::$connection->quote("$id");
        $status = static::$connection->quote("$status");

        return static::$connection->query("
            UPDATE `" . static::$tableName . "` SET `status` = $status WHERE `id` = $id
        ")->rowCount() > 0;
    }

    public static function deleteAllJobs()
    {
        static::init();

        return static::$connection->query("DELETE FROM `" . static::$tableName . "`")->rowCount();
    }

    public static function deleteAllJobsByWebsiteId($websiteId)
    {
        static::init();

        $websiteId = static::$connection->quote($websiteId);

        return static::$connection->query("DELETE FROM `" . static::$tableName . "` WHERE `website_id` = $websiteId")->rowCount();
    }
}
