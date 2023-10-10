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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Observer;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\Collection;
use Psr\Log\LoggerInterface;

/**
 * Class ProductClick
 * @package Mageplaza\ProductFeed\Observer
 */
class ProductClick implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SessionFactory
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Http $request
     * @param Collection $collection
     * @param Data $helperData
     * @param SessionFactory $checkoutSession
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        Http $request,
        Collection $collection,
        Data $helperData,
        SessionFactory $checkoutSession,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->request            = $request;
        $this->helperData         = $helperData;
        $this->collection         = $collection;
        $this->resourceConnection = $resourceConnection;
        $this->logger             = $logger;
        $this->checkoutSession    = $checkoutSession;
        $this->storeManager       = $storeManager;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $product     = $observer->getData('product');
        $storeId     = $this->storeManager->getStore()->getId();
        $sessionData = $this->checkoutSession->create()->getFeedKey() ?: [];
        $feedId      = null;
        $feedKey     = $this->request->getParam('mp_feed') ? $this->request->getParam('mp_feed') : null;
        if ($feedKey) {
            $feedId = $this->helperData->feedKeyDecode($feedKey);
            if ($this->checkFeedKeyExist($sessionData, $product, $feedKey)) {
                $sessionData[] = [
                    'product_id' => $product->getId(),
                    'key'        => $feedKey
                ];
            }
            $this->checkoutSession->create()->setFeedKey($sessionData);
        }

        $collection = $this->collection->addFieldToFilter('status', 1);
        $this->helperData->addStoreFilter($collection, $storeId);

        $updateData = [];

        if ($collection->getSize()) {
            foreach ($collection as $feed) {
                $feedData = $feed->getData();
                if ($feed->getConditions()->validate($product)) {
                    if ($feedId && $feed->getId() == $feedId) {
                        $feedData['click'] += 1;
                    }
                    $feedData['impression'] += 1;
                    $feedData['ctr']        = ($feedData['impression'] > 0)
                        ? (float) $feedData['click'] / $feedData['impression'] * 100 : 0;
                }
                $updateData[] = $feedData;
            }
        }

        if (count($updateData)) {
            $table = $collection->getMainTable();
            try {
                $this->resourceConnection->getConnection()->insertOnDuplicate($table, $updateData);
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * @param array $sessionData
     * @param Product $product
     * @param string $feedKey
     *
     * @return bool
     */
    protected function checkFeedKeyExist($sessionData, $product, $feedKey)
    {
        if (count($sessionData) <= 0) {
            return true;
        }
        foreach ($sessionData as $data) {
            if ($data['product_id'] == $product->getId() && $data['key'] == $feedKey) {
                return false;
            }
        }

        return true;
    }
}
