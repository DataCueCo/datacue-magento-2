<?php

namespace DataCue\MagentoModule\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('datacue_queue');
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'website_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Website ID'
                )
                ->addColumn(
                    'action',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false],
                    'Action'
                )
                ->addColumn(
                    'model',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false],
                    'Model'
                )
                ->addColumn(
                    'model_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Model ID'
                )
                ->addColumn(
                    'job',
                    Table::TYPE_TEXT,
                    8096,
                    ['nullable' => false],
                    'Job'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => 0],
                    'Status'
                )
                ->addColumn(
                    'executed_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Executed at'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created at'
                )
                ->setComment('DataCue Queue Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8mb4')
                ->setOption('collate', 'utf8mb4_general_ci');
            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('datacue_clients');
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'website_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => false,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Website ID'
                )
                ->addColumn(
                    'api_key',
                    Table::TYPE_TEXT,
                    64,
                    ['nullable' => false],
                    'API KEY'
                )
                ->addColumn(
                    'api_secret',
                    Table::TYPE_TEXT,
                    64,
                    ['nullable' => false],
                    'API SECRET'
                )
                ->addColumn(
                    'last_updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Last updated at'
                )
                ->setComment('DataCue Clients Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8mb4')
                ->setOption('collate', 'utf8mb4_general_ci');
            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('datacue_client_options');
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'website_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Website ID'
                )
                ->addColumn(
                    'key',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'API KEY'
                )
                ->addColumn(
                    'value',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'API SECRET'
                )
                ->addColumn(
                    'last_updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Last updated at'
                )
                ->setComment('DataCue Client Options Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8mb4')
                ->setOption('collate', 'utf8mb4_general_ci');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
