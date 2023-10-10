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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\Collection as FeedCollection;
use Psr\Log\LoggerInterface;

/**
 * Class AddCreditmemoReports
 * @package Mageplaza\ProductFeed\Observer
 */
class AddCreditmemoReports implements ObserverInterface
{
    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param FeedCollection $feedCollection
     * @param Data $helperData
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        FeedCollection $feedCollection,
        Data $helperData,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->feedCollection     = $feedCollection;
        $this->helperData         = $helperData;
        $this->resourceConnection = $resourceConnection;
        $this->logger             = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $storeId    = $creditmemo->getStoreId();
        $orderId    = $creditmemo->getOrderId();

        $feedCollection = $this->feedCollection->addFieldToFilter('status', 1);
        $this->helperData->addStoreFilter($feedCollection, $storeId);

        $items      = $creditmemo->getAllItems();
        $reportData = [];
        foreach ($items as $item) {
            if (!$item->getOrderItem()->getMpProductfeedKey()) {
                continue;
            }
            $orderItemFeedKeys = explode(',', $item->getOrderItem()->getMpProductfeedKey());
            $feedIds           = [];
            foreach ($orderItemFeedKeys as $feedKey) {
                $feedIds[] = $this->helperData->feedKeyDecode($feedKey);
            }
            foreach ($feedCollection as $feed) {
                if (in_array($feed->getId(), $feedIds)) {
                    $addData      = [
                        'feed_id'          => $feed->getId(),
                        'order_id'         => $orderId,
                        'ordered_quantity' => -1 * $item->getQty(),
                        'revenue'          => 0,
                        'refunded'         => $item->getBaseRowTotal(),
                        'discount'         => -1 * $item->getBaseDiscountAmount(),
                        'tax'              => -1 * $item->getBaseTaxAmount(),
                        'created_at'       => $creditmemo->getCreatedAt()
                    ];
                    $reportData[] = $addData;
                }
            }
        }
        if (count($reportData)) {
            $tableName = $this->resourceConnection->getTableName('mageplaza_productfeed_reports');
            try {
                $this->resourceConnection->getConnection()->insertMultiple($tableName, $reportData);
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        return $this;
    }
}
