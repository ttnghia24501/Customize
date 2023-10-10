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

namespace Mageplaza\Customize\Setup;

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

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            if (!$installer->tableExists('mageplaza_productfeed_generate')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_productfeed_generate'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ], 'Generate Id')
                    ->addColumn('profile_id', Table::TYPE_INTEGER, null, [
                        'nullable' => false,
                        'unsigned' => true
                    ], 'Profile Id')
                    ->addColumn('product_attributes', Table::TYPE_TEXT, null, ['nullable' => true], 'Object Max Items')
                    ->addColumn('product_chunk', Table::TYPE_TEXT, null, ['nullable' => true], 'Object Chunk')
                    ->addColumn('product_count', Table::TYPE_TEXT, null, ['nullable' => true], 'Object Count')
                    ->addColumn('template_html', Table::TYPE_TEXT, null, ['nullable' => true], 'Object Count')
                    ->addColumn('secret_key', Table::TYPE_TEXT, 900, ['nullable' => true], 'secret Key')
                    ->setComment('Product Feed Profile Generate Table');

                $connection->createTable($table);
            }
            if ($installer->tableExists('mageplaza_productfeed_feed')) {
                $connection->addColumn(
                    $installer->getTable('mageplaza_productfeed_feed'),
                    'is_cron_generating',
                    [
                        'type'    => Table::TYPE_INTEGER,
                        'comment' => 'Check if cron is generating'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
