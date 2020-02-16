<?php

namespace DataCue\MagentoModule\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use DataCue\MagentoModule\Website;

class Uninstall implements UninstallInterface
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        // drop datacue_queue
        $tableName = $setup->getTable('datacue_queue');
        if ($setup->getConnection()->isTableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }

        // drop datacue_clients
        $tableName = $setup->getTable('datacue_clients');
        if ($setup->getConnection()->isTableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }

        // drop datacue_client_options
        $tableName = $setup->getTable('datacue_client_options');
        if ($setup->getConnection()->isTableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }

        $websiteIds = Websites::getActiveWebsiteIds();
        foreach ($websiteIds as $id) {
            $credentials = Website::getApiKeyAndApiSecretByWebsiteId($id);
            if (!empty($credentials)) {
                $client = new \DataCue\Client(
                    $credentials['api_key'],
                    $credentials['api_secret'],
                    ['max_try_times' => 3],
                    file_exists(__DIR__ . '/../staging') ? 'development' : 'production'
                );
                try {
                    $client->client->clear();
                } catch (\Exception $e) {
        
                }
            }
        }

        $setup->endSetup();
    }
}