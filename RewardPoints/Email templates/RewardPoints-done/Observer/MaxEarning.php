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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Config\Source\MaxType;

/**
 * Class MaxEarning
 * @package Mageplaza\RewardPoints\Observer
 */
class MaxEarning implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MaxEarning constructor.
     *
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helperData,
        StoreManagerInterface $storeManager
    ) {
        $this->helperData   = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $items = $observer->getEvent()->getItems();
        if ($quote->getMpRewardReferralEarn()) {
            return;
        }
        $storeId          = $this->storeManager->getStore()->getId();
        $maxEarningType   = $this->helperData->getConfigEarning('type_max_earning_point', $storeId);
        $maxEarning       = $this->helperData->getConfigEarning('max_earning_point', $storeId);
        $oldTotalEarn     = $quote->getMpRewardEarn();
        $maxEarningAmount = 0;

        if ($maxEarningType == MaxType::FIXED) {
            if ($oldTotalEarn > $maxEarning && $maxEarning > 0) {
                $maxEarningAmount = $this->helperData->getPointHelper()->round($maxEarning);
                $quote->setMpRewardEarn($maxEarningAmount);
            }
        } else {
            if ($maxEarning > 0) {
                $subTotal = $quote->getBaseSubtotal();
                if ($this->helperData->getConfigEarning('earning_point_with_coupon')) {
                    if ($quote->getCouponCode()) {
                        $subTotal = $quote->getBaseSubtotalWithDiscount();
                    }
                }
                if ($this->helperData->isEarnPointFromShipping() && $quote->getShippingAddress()) {
                    $subTotal += $quote->getShippingAddress()->getBaseShippingAmount();
                }
                if ($this->helperData->isEarnPointFromTax()) {
                    if ($quote->getShippingAddress()) {
                        $subTotal += $quote->getShippingAddress()->getBaseTaxAmount();
                    } else {
                        if ($quote->getBillingAddress()) {
                            $subTotal += $quote->getBillingAddress()->getBaseTaxAmount();
                        }
                    }
                }
                if ($this->helperData->isEarnWithSpent() && $quote->getMpRewardBaseDiscount()) {
                    $subTotal -= $quote->getMpRewardBaseDiscount();
                }
                $maxEarningAmount = $subTotal * $maxEarning / 100;
                if ($oldTotalEarn > $maxEarningAmount) {
                    $maxEarningAmount = $this->helperData->getPointHelper()->round($maxEarningAmount);
                    $quote->setMpRewardEarn($maxEarningAmount);
                }
            }
        }

        if ($maxEarningAmount > 0 && $quote->getId()) {
            foreach ($items as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                $itemEarnPoint    = $item->getMpRewardEarn();
                $newItemEarnPoint = 0;
                if ($this->helperData->isEarnPointFromShipping($storeId)) {
                    $shippingEarn     = $quote->getMpRewardShippingEarn();
                    if ($oldTotalEarn > $shippingEarn) {
                        $newItemEarnPoint = $itemEarnPoint / ($oldTotalEarn - $shippingEarn) * $maxEarningAmount;
                    }
                } elseif ($oldTotalEarn > 0) {
                    $newItemEarnPoint = $itemEarnPoint / $oldTotalEarn * $maxEarningAmount;
                }

                $newItemEarnPoint = $this->helperData->getPointHelper()->round($newItemEarnPoint);
                $item->setMpRewardEarn($newItemEarnPoint);
                $item->save();
            }
            if ($this->helperData->isEarnPointFromShipping($storeId)) {
                $quote->setMpRewardShippingEarn(0);
            }
        }
    }
}
