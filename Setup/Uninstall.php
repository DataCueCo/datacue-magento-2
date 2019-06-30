<?php

namespace DataCue\MagentoModule\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\ObjectManager;

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
        $tableName = $setup->getTable('datacue_queue');
        if ($setup->getConnection()->isTableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }

        // clear client
        $objectManager = ObjectManager::getInstance();
        $this->collectionFactory = $objectManager->create('Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory');
        $client = new \DataCue\Client(
            $this->getApiKey(),
            $this->getApiSecret(),
            ['max_try_times' => 3],
            file_exists(__DIR__ . '/../staging') ? 'development' : 'production'
        );
        try {
            $client->client->clear();
        } catch (\Exception $e) {

        }

        $setup->endSetup();
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
}