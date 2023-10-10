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
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\ProductFeed\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (!$installer->tableExists('mageplaza_productfeed_feed')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_productfeed_feed'))
                ->addColumn('feed_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ], 'Feed Id')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Name')
                ->addColumn('status', Table::TYPE_INTEGER, 1, ['nullable' => false], 'Feed Status')
                ->addColumn('store_id', Table::TYPE_TEXT, 64, ['nullable' => false], 'Store')
                ->addColumn('file_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'File Name')
                ->addColumn('file_type', Table::TYPE_TEXT, 64, ['nullable' => false], 'Feed Type')
                ->addColumn('template_html', Table::TYPE_TEXT, '2M', [], 'Template Html')
                ->addColumn('field_separate', Table::TYPE_TEXT, 64, [], 'Field Separate')
                ->addColumn('field_around', Table::TYPE_TEXT, 64, [], 'Field Around')
                ->addColumn('include_header', Table::TYPE_INTEGER, 1, [], 'Include Field Header')
                ->addColumn('fields_map', Table::TYPE_TEXT, '2M', [], 'Field Map')
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Product Filter')
                ->addColumn('category_map', Table::TYPE_TEXT, '2M', [], 'Category Map')
                ->addColumn('execution_mode', Table::TYPE_TEXT, 64, [], 'Execution Mode')
                ->addColumn('frequency', Table::TYPE_TEXT, 64, [], 'Frequency')
                ->addColumn('cron_run_day_of_week', Table::TYPE_TEXT, 64, [], 'Day of Week')
                ->addColumn('cron_run_day_of_month', Table::TYPE_TEXT, 64, [], 'Day of Month')
                ->addColumn('cron_run_time', Table::TYPE_TEXT, 64, [], 'Cron Run Time')
                ->addColumn('last_cron', Table::TYPE_TIMESTAMP, null, [], 'Last Generated')
                ->addColumn('delivery_enable', Table::TYPE_INTEGER, 1, [], 'Delivery Enable')
                ->addColumn('protocol', Table::TYPE_TEXT, 64, [], 'Delivery Config: Protocol')
                ->addColumn('passive_mode', Table::TYPE_TEXT, 64, [], 'Delivery Config: Passive Mode')
                ->addColumn('host_name', Table::TYPE_TEXT, 255, [], 'Delivery Config: Host Name')
                ->addColumn('user_name', Table::TYPE_TEXT, 255, [], 'Delivery Config: User Name')
                ->addColumn('password', Table::TYPE_TEXT, 255, [], 'Delivery Config: Password')
                ->addColumn('directory_path', Table::TYPE_TEXT, 255, [], 'Delivery Config: Directory Path')
                ->addColumn('campaign_source', Table::TYPE_TEXT, 255, [], 'Google Analytics: Campaign Source')
                ->addColumn('campaign_medium', Table::TYPE_TEXT, 255, [], 'Google Analytics: Campaign Medium')
                ->addColumn('campaign_name', Table::TYPE_TEXT, 255, [], 'Google Analytics: Campaign Name')
                ->addColumn('campaign_term', Table::TYPE_TEXT, 255, [], 'Google Analytics: Campaign Term')
                ->addColumn('campaign_content', Table::TYPE_TEXT, 255, [], 'Google Analytics: Campaign Content')
                ->addColumn('last_generated', Table::TYPE_TIMESTAMP, null, [], 'Last Generated')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Update At'
                )
                ->setComment('Product Feed Table');

            $connection->createTable($table);
        }
        if (!$installer->tableExists('mageplaza_productfeed_defaulttemplate')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_productfeed_defaulttemplate'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ], 'Template Id')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Template Name')
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Template Title')
                ->addColumn('file_type', Table::TYPE_TEXT, 64, ['nullable' => false], 'Type')
                ->addColumn('template_html', Table::TYPE_TEXT, '2M', [], 'Template Html')
                ->addColumn('field_separate', Table::TYPE_TEXT, 64, [], 'Field Separate')
                ->addColumn('field_around', Table::TYPE_TEXT, 64, [], 'Field Around')
                ->addColumn('include_header', Table::TYPE_INTEGER, 1, [], 'Include Field Header')
                ->addColumn('fields_map', Table::TYPE_TEXT, '2M', [], 'Field Map')
                ->setComment('Default Template Table');

            $connection->createTable($table);
        }
        if (!$installer->tableExists('mageplaza_productfeed_history')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_productfeed_history'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ], 'Log Id')
                ->addColumn('feed_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Feed Id')
                ->addColumn('feed_name', Table::TYPE_TEXT, 255, [], 'Feed Name')
                ->addColumn('status', Table::TYPE_TEXT, 64, [], 'Log Status')
                ->addColumn('delivery', Table::TYPE_TEXT, 64, [], 'Delivery Status')
                ->addColumn('type', Table::TYPE_TEXT, 64, ['nullable' => false], 'Execution Type')
                ->addColumn('file', Table::TYPE_TEXT, 255, [], 'File')
                ->addColumn('product_count', Table::TYPE_INTEGER, null, [], 'Product Count')
                ->addColumn('success_message', Table::TYPE_TEXT, 255, [], 'Success Message')
                ->addColumn('error_message', Table::TYPE_TEXT, 255, [], 'Error Message')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addIndex($installer->getIdxName('mageplaza_productfeed_history', ['feed_id']), ['feed_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_productfeed_history',
                        'feed_id',
                        'mageplaza_productfeed_feed',
                        'feed_id'
                    ),
                    'feed_id',
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'feed_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Product Feed Table');

            $connection->createTable($table);
        }
        $installer->endSetup();
    }
}
