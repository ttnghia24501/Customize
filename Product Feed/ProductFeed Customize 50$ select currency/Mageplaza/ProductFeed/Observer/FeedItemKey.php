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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Observer;

use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Mageplaza\ProductFeed\Helper\Data as HelperData;

/**
 * Class FeedItemKey
 * @package Mageplaza\ProductFeed\Observer
 */
class FeedItemKey implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var SessionFactory
     */
    protected $checkoutSession;

    /**
     * RewardPointsConvertData constructor.
     *
     * @param HelperData $helperData
     * @param SessionFactory $checkoutSession
     */
    public function __construct(
        HelperData $helperData,
        SessionFactory $checkoutSession
    ) {
        $this->helperData      = $helperData;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var Order $order */
        $order        = $observer->getEvent()->getOrder();
        $sessionData  = $this->checkoutSession->create()->getFeedKey();
        $unsetIndexes = [];

        if (is_array($sessionData) && count($sessionData)) {
            foreach ($order->getItems() as $item) {
                $feedKeys = [];
                foreach ($sessionData as $index => $itemKey) {
                    if ($item->getProductId() == $itemKey['product_id']) {
                        $feedKeys[]     = $itemKey['key'];
                        $unsetIndexes[] = $index;
                    }
                }
                $feedKeys = implode(',', $feedKeys);
                $item->setMpProductfeedKey($feedKeys);
            }

            $newSessionData = array_diff_key($sessionData, array_flip($unsetIndexes));
            count($newSessionData) ? $this->checkoutSession->create()->setFeedKey($newSessionData)
                : $this->checkoutSession->create()->unsFeedKey();
        }

        return $this;
    }
}
