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
declare(strict_types=1);

namespace Mageplaza\ProductFeed\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\CollectionFactory as FeedCollection;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 *
 * Class DefaultMapping
 * @package Mageplaza\ProductFeed\Setup\Patch\Data
 */
class DefaultMapping implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FeedCollection $feedCollection
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        FeedCollection $feedCollection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->feedCollection  = $feedCollection;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $mappingDefault = '{"id":{"value":"{{sku}}","type":"string","default":"","description":"","required":"1"},"title":{"value":"{{name}}","type":"string","default":"","description":"","required":""},"brand":{"value":"{{manufacturer}}","type":"string","default":"","description":"","required":""},"description":{"value":"{{description}}","type":"string","default":"","description":"","required":""},"link":{"value":"{{link}}","type":"string","default":"","description":"","required":""},"imageLink":{"value":"{{image_link}}","type":"string","default":"","description":"","required":""},"additionalImageLinks":{"value":"{{images}}","type":"string","default":"","description":"","required":""},"price":{"value":"{{final_price}}","type":"object","default":"0","description":"","required":""},"availability":{"value":"{{quantity_and_stock_status}}","type":"string","default":"","description":"","required":""},"condition":{"value":"","type":"string","default":"","description":"","required":""},"targetCountry":{"value":"US","type":"string","default":"","description":"","required":"1"},"contentLanguage":{"value":"en","type":"string","default":"","description":"","required":"1"},"channel":{"value":"online","type":"string","default":"","description":"","required":"1"},"identifierExists":{"value":"1","type":"boolean","default":"1","description":"","required":""},"gtin":{"value":"","type":"string","default":"","description":"","required":""},"shippingLabel":{"value":"","type":"string","default":"","description":"","required":""},"promotionIds":{"value":"","type":"string","default":"","description":"","required":""},"customLabel0":{"value":"","type":"string","default":"","description":"","required":""},"customLabel1":{"value":"","type":"string","default":"","description":"","required":""},"customLabel2":{"value":"","type":"string","default":"","description":"","required":""},"customLabel3":{"value":"","type":"string","default":"","description":"","required":""},"customLabel4":{"value":"","type":"string","default":"","description":"","required":""},"googleProductCategory":{"value":"{{mapping}}","type":"string","default":"","description":"","required":""},"productTypes":{"value":"{{category_path}}","type":"string","default":"","description":"","required":""},"ageGroup":{"value":"","type":"string","default":"","description":"","required":""},"color":{"value":"","type":"string","default":"","description":"","required":""},"gender":{"value":"","type":"string","default":"","description":"","required":""},"sizes":{"value":"","type":"string","default":"","description":"","required":""}}';
        $feedCollection = $this->feedCollection->create();
        if ($feedCollection->getSize()) {
            $updateData = [];
            foreach ($feedCollection as $feed) {
                $feedData = $feed->getData();
                if (!$feedData['mapping']) {
                    $feedData['mapping'] = $mappingDefault;
                }
                $updateData[] = $feedData;
            }
            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                $this->moduleDataSetup->getTable('mageplaza_productfeed_feed'),
                $updateData
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.3';
    }
}
