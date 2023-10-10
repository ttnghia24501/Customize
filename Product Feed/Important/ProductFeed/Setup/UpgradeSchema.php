<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 * @package Mageplaza\ProductFeed\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            if ($installer->tableExists('mageplaza_productfeed_feed')) {
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'compress_file',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'comment' => 'Compress File',
                        'after'   => 'file_type'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            if ($installer->tableExists('mageplaza_productfeed_feed')) {
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'private_key_path',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'comment' => 'Private Key File',
                        'after'   => 'password'
                    ]
                );

                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'mapping',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'comment' => 'Mapping',
                        'after'   => 'private_key_path'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            if ($installer->tableExists('mageplaza_productfeed_feed')) {
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'preview_limit',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'unsigned' => true,
                        'comment'  => 'Preview Limit',
                        'after'    => 'conditions_serialized'
                    ]
                );
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'click',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'default'  => 0,
                        'nullable' => false,
                        'comment'  => 'Click',
                        'after'    => 'campaign_content'
                    ]
                );
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'impression',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => false,
                        'default'  => 0,
                        'comment'  => 'Impression',
                        'after'    => 'click'
                    ]
                );
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'ctr',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => false,
                        'default'  => 0,
                        'comment'  => 'CTR',
                        'after'    => 'impression'
                    ]
                );
            }

            if (!$installer->tableExists('mageplaza_productfeed_reports')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_productfeed_reports'))
                    ->addColumn('report_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true
                    ], 'Report Id')
                    ->addColumn('feed_id', Table::TYPE_INTEGER, null, [
                        'nullable' => false,
                        'unsigned' => true
                    ], 'Feed Id')
                    ->addColumn('order_id', Table::TYPE_INTEGER, null, [
                        'nullable' => false,
                        'unsigned' => true
                    ], 'Order ID')
                    ->addColumn('ordered_quantity', Table::TYPE_DECIMAL, '12,4', [], 'Ordered Quantity')
                    ->addColumn('revenue', Table::TYPE_DECIMAL, '20,4', [], 'Revenue')
                    ->addColumn('refunded', Table::TYPE_DECIMAL, '20,4', [], 'Refunded')
                    ->addColumn('discount', Table::TYPE_DECIMAL, '20,4', [], 'Discount')
                    ->addColumn('tax', Table::TYPE_DECIMAL, '20,4', [], 'Tax')
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Created At'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_productfeed_reports',
                            'feed_id',
                            'mageplaza_productfeed_feed',
                            'feed_id'
                        ),
                        'feed_id',
                        $installer->getTable('mageplaza_productfeed_feed'),
                        'feed_id',
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_productfeed_reports',
                            'order_id',
                            'sales_order',
                            'entity_id'
                        ),
                        'order_id',
                        $installer->getTable('sales_order'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )
                    ->setComment('ProductFeed Reports');

                $connection->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            if ($installer->tableExists('mageplaza_productfeed_feed')) {
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'request_url',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'comment' => 'Request Url',
                        'after'   => 'directory_path'
                    ]
                );
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'headers',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'comment' => 'Headers',
                        'after'   => 'request_url'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
