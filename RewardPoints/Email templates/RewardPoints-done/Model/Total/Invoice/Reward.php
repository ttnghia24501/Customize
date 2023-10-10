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

namespace Mageplaza\RewardPoints\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Mageplaza\RewardPoints\Helper\Calculation;

/**
 * Class Reward
 * @package Mageplaza\RewardPoints\Model\Total\Invoice
 */
class Reward extends AbstractTotal
{
    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * Reward constructor.
     *
     * @param Calculation $calculation
     * @param array $data
     */
    public function __construct(
        Calculation $calculation,
        array $data = []
    ) {
        $this->calculation = $calculation;

        parent::__construct($data);
    }

    /**
     * @param Invoice $invoice
     *
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $invoice->setMpRewardEarn(0);
        $invoice->setMpRewardBaseDiscount(0);
        $invoice->setMpRewardDiscount(0);

        $totalEarningPoint       = 0;
        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;

        $order               = $invoice->getOrder();
        $canEarnAfterInvoice = (bool) $order->getMpRewardEarnAfterInvoice();
        $addShippingDiscount = true;
        $fields              = ['mp_reward_earn', 'mp_reward_discount', 'mp_reward_base_discount'];
        $itemInvoice         = $this->calculation->getOldRewardData(
            $order->getInvoiceCollection(),
            $fields,
            $addShippingDiscount
        );

        /**
         * Calculate reward shipping(earn,discount)
         */
        if ($addShippingDiscount) {
            $totalEarningPoint += $canEarnAfterInvoice ? $order->getMpRewardShippingEarn() : 0;
        }

        /** @var $item Item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemQty = $orderItem->getQtyOrdered();
            if ($orderItemQty) {
                $this->calculateRewardDiscount(
                    $invoice,
                    $orderItem,
                    $item,
                    $itemInvoice,
                    $totalDiscountAmount,
                    $baseTotalDiscountAmount
                );
                $totalEarningPoint += $this->calculateRewardEarn(
                    $item,
                    $canEarnAfterInvoice,
                    $orderItem,
                    $itemInvoice,
                    $orderItemQty
                );
            }
        }

        $invoice->setMpRewardEarn($totalEarningPoint);
        $invoice->setMpRewardDiscount($totalDiscountAmount);
        $invoice->setMpRewardBaseDiscount($baseTotalDiscountAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);

        return $this;
    }

    /**
     * @param Item $item
     * @param bool $canEarnAfterInvoice
     * @param OrderItem $orderItem
     * @param array $itemInvoice
     * @param int $orderItemQty
     *
     * @return float|int
     */
    public function calculateRewardEarn($item, $canEarnAfterInvoice, $orderItem, $itemInvoice, $orderItemQty)
    {
        $orderItemEarn     = (int) $orderItem->getMpRewardEarn();
        $totalEarningPoint = 0;

        if ($canEarnAfterInvoice && $orderItemEarn) {
            $orderItemId = $orderItem->getId();
            $itemEarn    = $orderItemEarn
                - (isset($itemInvoice[$orderItemId]) ? $itemInvoice[$orderItemId]['mp_reward_earn'] : 0);
            if (!$item->isLast()) {
                $activeQty = $orderItemQty - $orderItem->getQtyInvoiced();
                $itemEarn  = floor($itemEarn / $activeQty * $item->getQty());
            }

            $item->setMpRewardEarn($itemEarn);

            $totalEarningPoint += $itemEarn;
        }

        return $totalEarningPoint;
    }

    /**
     * @param Invoice $invoice
     * @param OrderItem $orderItem
     * @param Item $item
     * @param array $itemInvoice
     * @param int $totalDiscountAmount
     * @param int $baseTotalDiscountAmount
     */
    public function calculateRewardDiscount(
        $invoice,
        $orderItem,
        $item,
        $itemInvoice,
        &$totalDiscountAmount,
        &$baseTotalDiscountAmount
    ) {
        $orderItemDiscount     = (double) $orderItem->getMpRewardDiscount();
        $baseOrderItemDiscount = (double) $orderItem->getMpRewardBaseDiscount();
        $orderItemQty          = $orderItem->getQtyOrdered();
        $orderItemId           = $orderItem->getId();
        if ($orderItemDiscount) {
            $discount     = $orderItemDiscount
                - (isset($itemInvoice[$orderItemId]) ? $itemInvoice[$orderItemId]['mp_reward_discount'] : 0);
            $baseDiscount = $baseOrderItemDiscount
                - (isset($itemInvoice[$orderItemId]) ? $itemInvoice[$orderItemId]['mp_reward_base_discount'] : 0);
            if (!$item->isLast()) {
                $activeQty    = $orderItemQty - $orderItem->getQtyInvoiced();
                $discount     = $this->calculation->roundPrice(
                    $discount / $activeQty * $item->getQty(),
                    'regular'
                );
                $baseDiscount = $this->calculation->roundPrice(
                    $baseDiscount / $activeQty * $item->getQty(),
                    'base'
                );
            }

            $item->setMpRewardDiscount($discount);
            $item->setMpRewardBaseDiscount($baseDiscount);

            $totalDiscountAmount     += $discount;
            $baseTotalDiscountAmount += $baseDiscount;
        }
    }
}
