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

use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class SalesOrderInvoiceSaveAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class SalesOrderInvoiceSaveAfter implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * SalesOrderInvoiceSaveAfter constructor.
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
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        /* @var $invoice Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        if (!$invoice->getOrder()->getCustomerIsGuest()) {
            $maxBalance  = $this->helperData->getMaxPointPerCustomer($invoice->getStoreId());
            $account     = $this->helperData->getAccountHelper()->create($invoice->getOrder()->getCustomerId());
            $pointAmount = $invoice->getMpRewardEarn();
            if ($pointAmount > 0 && $this->helperData->isEarnPointAfterInvoiceCreated()) {
                $invoice = $this->helperData->updateRewardEarnWithMaxBalance(
                    $invoice,
                    $account,
                    $maxBalance,
                    $pointAmount
                );
                $order   = $this->helperData->updateRewardEarnWithMaxBalance(
                    $invoice->getOrder(),
                    $account,
                    $maxBalance,
                    $pointAmount
                );

                $invoice->setOrder($order);

                if ($invoice->getMpRewardEarn()) {
                    $this->helperData->addTransaction(
                        HelperData::ACTION_EARNING_ORDER,
                        $invoice->getOrder()->getCustomerId(),
                        $invoice->getMpRewardEarn(),
                        $invoice->getOrder()
                    );
                }
            }
        }
    }
}
