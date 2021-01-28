<?php

namespace Shippop\Ecommerce\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('sales_order_shippop')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('sales_order_shippop')
            )
               ->addColumn(
                   'order_id',
                   \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                   null,
                   [
                    'identity' => false,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                   ],
                   'Order ID'
               )
               ->addColumn(
                   'shippop_status',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                   50,
                   [],
                   'SHIPPOP Status'
               )
               ->addColumn(
                   'courier_code',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                   20,
                   [],
                   'Courier Code'
               )
               ->addColumn(
                   'tracking_code',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                   255,
                   [],
                   'SHIPPOP Tracking Code'
               )
               ->addColumn(
                   'courier_tracking_code',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                   255,
                   [],
                   'Courier Tracking Code'
               )
               ->addColumn(
                   'purchase_id',
                   \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                   1,
                   [],
                   'SHIPPOP Purchase ID'
               )
               ->addColumn(
                   'confirm_purchase_status',
                   \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                   1,
                   [],
                   'SHIPPOP Purchase Confirm Status'
               )
               ->addColumn(
                   'environment_sandbox',
                   \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                   1,
                   [],
                   'Environment Sandbox'
               )
               ->addColumn(
                   'extra',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                   1000,
                   [],
                   'Extran Datas'
               )
               ->addColumn(
                   'created_at',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                   null,
                   [
                    'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                   ],
                   'Created At'
               )->addColumn(
                   'updated_at',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                   null,
                   [
                    'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                   ],
                   'Updated At'
               )->setComment('Courier Table');
                
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('sales_order_shippop'),
                $setup->getIdxName(
                    $installer->getTable('sales_order_shippop'),
                    ['tracking_code','courier_tracking_code','shippop_status', 'courier_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['tracking_code','courier_tracking_code','shippop_status', 'courier_code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $installer->endSetup();
    }
}
