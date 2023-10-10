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
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class OrderCancelAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * OrderCancelAfter constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();
        $pointRefund = $order->getMpRewardSpent();
        if ($pointRefund > 0) {
            if ($order->hasInvoices()) {
                $pointRefund -= $order->getMpRewardSpentInvoiced();
            }
            if ($pointRefund > 0) {
                $this->helperData->addTransaction(
                    HelperData::ACTION_SPENDING_REFUND,
                    $order->getCustomerId(),
                    $pointRefund,
                    $order
                );
            }
        }
    }
}
