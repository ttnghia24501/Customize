<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\RewardPoints\Model\Total\Creditmemo;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item\Collection;
use Mageplaza\RewardPoints\Helper\Calculation;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class Reward
 * @package Mageplaza\RewardPoints\Model\Total\Creditmemo
 */
class Reward extends AbstractTotal
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * @var InvoiceItem
     */
    protected $invoiceItem;

    /**
     * Reward constructor.
     *
     * @param Data $helperData
     * @param Calculation $calculation
     * @param InvoiceItem $invoiceItem
     * @param array $data
     */
    public function __construct(
        Data $helperData,
        Calculation $calculation,
        InvoiceItem $invoiceItem,
        array $data = []
    ) {
        $this->helperData  = $helperData;
        $this->calculation = $calculation;
        $this->invoiceItem = $invoiceItem;

        parent::__construct($data);
    }

    /**
     * @param Creditmemo $creditmemo
     *
     * @return $this|AbstractTotal
     */
    public function collect(Creditmemo $creditmemo)
    {
        $creditmemo->setMpRewardEarn(0);
        $creditmemo->setMpRewardBaseDiscount(0);
        $creditmemo->setMpRewardDiscount(0);
        $order                    = $creditmemo->getOrder();
        $mpRewardEarnAfterInvoice = (bool) $order->getMpRewardEarnAfterInvoice();

        if ((!$order->getMpRewardEarn() && !$order->getMpRewardSpent())) {
            return $this;
        }

        $totalEarningPoint       = 0;
        $totalSpendingPoint      = 0;
        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;
        $isRefundPointEarn       = $this->helperData->getPointHelper()->isRefundPointsEarn($order->getStoreId());
        $isRefundPointSpent      = $this->helperData->getPointHelper()->isRestorePointAfterRefund($order->getStoreId());
        $isAddRewardShippingEarn = false;
        $itemCreditmemo          = $this->calculation->getOldRewardData(
            $order->getCreditmemosCollection(),
            ['mp_reward_earn', 'mp_reward_spent', 'mp_reward_base_discount', 'mp_reward_discount'],
            $isAddRewardShippingEarn
        );

        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy() || $item->getQty() <= 0) {
                continue;
            }

            if (!$mpRewardEarnAfterInvoice) {
                $rewardItem = $this->getItemRewardData($orderItem, $orderItem->getMpRewardSpent());
            } else {
                $invoiceItem = $this->invoiceItem->getCollection()
                    ->addFieldToFilter('order_item_id', ['eq' => $item->getOrderItemId()]);
                $rewardItem  = $this->getItemRewardData($invoiceItem, $orderItem->getMpRewardSpent(), true);
            }

            /**
             * Calculate point earn
             */
            if ($this->canRefundEarn($order, $isRefundPointEarn, $mpRewardEarnAfterInvoice)
                && $rewardItem->getMpRewardEarn() && $order->getMpRewardEarn() > 0) {
                $itemEarn = $this->calculatePoint('mp_reward_earn', $item, $orderItem, $rewardItem, $itemCreditmemo);
                $item->setMpRewardEarn($itemEarn);
                $totalEarningPoint += $itemEarn;
            }

            /**
             * Calculate point spent
             */
            if ($isRefundPointSpent && $rewardItem->getMpRewardSpent()) {
                $itemSpent = $this->calculatePoint('mp_reward_spent', $item, $orderItem, $rewardItem, $itemCreditmemo);
                $item->setMpRewardSpent($itemSpent);
                $totalSpendingPoint += $itemSpent;
            }

            /**
             * Calculate discount
             */
            if ($rewardItem->getMpRewardDiscount() > 0) {
                $itemDiscount     = $this->calculation->roundPrice(
                    ($rewardItem->getMpRewardDiscount() * $item->getQty()) / $rewardItem->getQty(),
                    'regular'
                );
                $itemBaseDiscount = $this->calculation->roundPrice(
                    ($rewardItem->getMpRewardBaseDiscount() * $item->getQty()) / $rewardItem->getQty(),
                    'base'
                );
                $item->setMpRewardDiscount($itemDiscount);
                $item->setMpRewardBaseDiscount($itemBaseDiscount);
                $totalDiscountAmount     += $itemDiscount;
                $baseTotalDiscountAmount += $itemBaseDiscount;
            }
        }

        /**
         * Calculate reward shipping(earn, spent, discount)
         */
        if ($creditmemo->getShippingAmount()) {
            if (abs(($creditmemo->getShippingAmount() + $order->getShippingRefunded())
                    - $order->getShippingAmount()) < 0.00001
            ) {
                if ($this->canRefundEarn($order, $isRefundPointEarn, $mpRewardEarnAfterInvoice)) {
                    $totalEarningPoint += $order->getMpRewardShippingEarn();
                }
                if ($isRefundPointSpent) {
                    $totalSpendingPoint += $order->getMpRewardShippingSpent();
                }
            }
        }

        $creditmemo->setMpRewardEarn($totalEarningPoint);
        $creditmemo->setMpRewardSpent($totalSpendingPoint);
        $creditmemo->setMpRewardDiscount($totalDiscountAmount);
        $creditmemo->setMpRewardBaseDiscount($baseTotalDiscountAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmount);
        if ($creditmemo->getGrandTotal() == 0) {
            if ($totalDiscountAmount > 0 || $totalEarningPoint > 0 || $totalSpendingPoint > 0) {
                $creditmemo->setAllowZeroGrandTotal(true);
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param Creditmemo\Item $item
     * @param Item $orderItem
     * @param Object $rewardItem
     * @param array $itemCreditmemo
     *
     * @return int
     */
    public function calculatePoint($field, $item, $orderItem, $rewardItem, $itemCreditmemo)
    {
        if ($item->getQty() + $orderItem->getQtyRefunded() == $orderItem->getQtyOrdered()) {
            $point = $orderItem->getData($field);
            if (isset($itemCreditmemo[$orderItem->getId()])) {
                $point -= $itemCreditmemo[$orderItem->getId()][$field];
            }
        } else {
            $point = floor(($rewardItem->getData($field) * $item->getQty()) / $rewardItem->getQty());
        }

        return $point;
    }

    /**
     * @param Order $order
     * @param string $isRefundPointEarn
     * @param bool $mpRewardEarnAfterInvoice
     *
     * @return bool
     */
    public function canRefundEarn($order, $isRefundPointEarn, $mpRewardEarnAfterInvoice)
    {
        return $isRefundPointEarn &&
            ($mpRewardEarnAfterInvoice ||
                (!$mpRewardEarnAfterInvoice && ($order->getState() == Order::STATE_COMPLETE))
            );
    }

    /**
     * @param Item|Collection $item
     * @param int $itemSpent
     * @param bool $isMultiInvoices
     *
     * @return DataObject
     */
    public function getItemRewardData($item, $itemSpent, $isMultiInvoices = false)
    {
        $rewardData           = new DataObject();
        $qty                  = 0;
        $mpRewardEarn         = 0;
        $mpRewardBaseDiscount = 0;
        $mpRewardDiscount     = 0;

        if ($isMultiInvoices) {
            /** @var InvoiceItem $it */
            foreach ($item as $it) {
                $qty                  += $it->getQty();
                $mpRewardEarn         += $it->getMpRewardEarn();
                $mpRewardBaseDiscount += $it->getMpRewardBaseDiscount();
                $mpRewardDiscount     += $it->getMpRewardDiscount();
            }
        } else {
            if ($item->getId()) {
                $qty                  = $item instanceof Item ? $item->getQtyOrdered() : $item->getQty();
                $mpRewardEarn         += $item->getMpRewardEarn();
                $mpRewardBaseDiscount += $item->getMpRewardBaseDiscount();
                $mpRewardDiscount     += $item->getMpRewardDiscount();
            }
        }

        $rewardData->setQty($qty)
            ->setMpRewardEarn($mpRewardEarn)
            ->setMpRewardSpent($itemSpent)
            ->setMpRewardBaseDiscount($mpRewardBaseDiscount)
            ->setMpRewardDiscount($mpRewardDiscount);

        return $rewardData;
    }
}
