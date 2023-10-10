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

namespace Mageplaza\Customize\Helper\ProductFeed;

use Mageplaza\ProductFeed\Helper\Data as FeedData;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Mageplaza\Customize\Model\ProductFeedGenerate;

class Data extends FeedData
{
    /**
     * Set Feed session data
     *
     * @param string|int $feedId
     * @param string $path
     * @param mixed $value
     */
    public function setFeedSessionDataForCommand($feedId, $path, $value)
    {
        if(gettype($value) == 'array') {
            $value = $this->objectManager->create(JsonHelper::class)->jsonEncode($value);
        }
        $feedGenerateData     = $this->objectManager->create(ProductFeedGenerate::class)->load($feedId, 'profile_id');
        $objectAttribute      = $path === 'product_attributes' ? $value : '';
        $objectChunk          = $path === 'product_chunk' ? $value : '';
        $objectCount          = $path === 'product_count' ? $value : '';
        $templateHtml         = $path === 'template_html' ? $value : '';
        if(!$feedGenerateData->getData()) {
            $dataGen = [
                'profile_id'        => $feedId,
                'product_attributes'  => $objectAttribute,
                'product_chunk'      => $objectChunk,
                'product_count'      => $objectCount,
                'template_html'      => $templateHtml,
            ];
            $this->objectManager->create(ProductFeedGenerate::class)->addData($dataGen)->save();
        } else {
            $feedGenerateData->setData($path, $value)->save();
        }
    }


    /**
     * Reset Feed session data
     *
     * @param string|int $feedId
     */
    public function resetFeedSessionDataForCommand($feedId)
    {
        $feedGenerateData = $this->objectManager->create(ProductFeedGenerate::class)->load($feedId, 'profile_id');
        if($feedGenerateData->getData()) {
            $feedGenerateData->delete();
        }
    }

    public function prepareGenerateForCommand($feed)
    {
        $feedId = $feed->getId();
        $this->resetFeedSessionDataForCommand($feedId);
        $template = $this->prepareTemplate($feed, null, true);
        $root     = $template->getRoot();
        $prdAttr  = [];
        $prdAttr  = $this->getProductAttr($root->getNodelist(), $prdAttr);
        $this->setFeedSessionDataForCommand($feedId, 'product_attributes', $prdAttr);
        $productIds = $feed->getMatchingProductIds();
        $chunk      = array_chunk($productIds, 300);
//        $this->setFeedSessionData($feedId, 'product_chunk', $chunk);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            $fileProcess = $objectManager->create('\Magento\Framework\Filesystem\Driver\File');
            if($fileProcess->isExists(self::FEED_FILE_PATH . 'collection/' . $feedId . '/')) {
                $fileProcess->deleteDirectory(self::FEED_FILE_PATH . 'collection/' . $feedId . '/');
            }
        } catch (Exception $e) {
            return [
                'product_count' => count($productIds),
                'array_chunk'   => $chunk
            ];
        }

        return [
            'product_count' => count($productIds),
            'array_chunk'   => $chunk
        ];
    }

    public function prepareProductDataForCommand($feed, $feedIds, $isLast = false)
    {
        $feedId       = $feed->getId();
        $productAttr  = $this->getFeedSessionDataForCommand($feedId, 'product_attributes');
        $productCount = (int) $this->getFeedSessionDataForCommand($feedId, 'product_count');
        $ids          = $feedIds;
        $collection   = $this->getProductsData($feed, $productAttr, $ids);
        $productCount += count($collection);
        $name         = $ids ? current($ids) . end($ids) : '0';
        $this->createFeedCollectionFile($feedId, self::jsonEncode($collection), $name);
        $this->setFeedSessionDataForCommand($feedId, 'product_count', $productCount);

        return [
            'complete'      => $isLast,
            'product_count' => $productCount
        ];
    }
}
