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

namespace Mageplaza\RewardPoints\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class RewardPointsConvertData
 * @package Mageplaza\RewardPoints\Observer
 */
class RewardPointsConvertData implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * RewardPointsConvertData constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($this->earnWithSpent($quote) && $quote->getMpRewardEarn() && $quote->getCustomerId()) {
            $order->setMpRewardEarn($quote->getMpRewardEarn())
                ->setMpRewardShippingEarn($quote->getMpRewardShippingEarn())
                ->setMpRewardEarnAfterInvoice(1);
        }

        if ($quote->getMpRewardDiscount()) {
            $order->setMpRewardDiscount($quote->getMpRewardDiscount())
                ->setMpRewardBaseDiscount($quote->getMpRewardBaseDiscount())
                ->setMpRewardShippingDiscount($quote->getMpRewardShippingDiscount())
                ->setMpRewardShippingBaseDiscount($quote->getMpRewardShippingBaseDiscount());
        }

        if ($quote->getMpRewardSpent() && $quote->getCustomerId()) {
            $order->setMpRewardSpent($quote->getMpRewardSpent())
                ->setMpRewardShippingSpent($quote->getMpRewardShippingSpent());
        }

        foreach ($order->getItems() as $item) {
            $quoteItem = $quote->getItemById($item->getQuoteItemId());
            if (!$quoteItem) {
                continue;
            }

            if ($quote->getCustomerId()) {
                $item->setMpRewardEarn($quoteItem->getMpRewardEarn())
                    ->setMpRewardSpent($quoteItem->getMpRewardSpent());
            }
            $item->setMpRewardBaseDiscount($quoteItem->getMpRewardBaseDiscount())
                ->setMpRewardDiscount($quoteItem->getMpRewardDiscount());
        }

        return $this;
    }

    /**
     * @param $quote
     *
     * @return bool
     */
    public function earnWithSpent($quote)
    {
        if ($this->helperData->isEarnWithSpent()) {
            return true;
        }
        // get null point if earnWithSpent is No && hasSpent.
        return $quote->getMpRewardSpent() ? false : true;
    }
}
