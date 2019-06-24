<?php

namespace DataCue\MagentoModule\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $tableName = $setup->getTable('datacue_queue');
        if ($setup->getConnection()->isTableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }
        $setup->endSetup();
    }
}