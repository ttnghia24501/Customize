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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\TransactionRepository;

/**
 * Class CreditmemoRefundSaveAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class CreditmemoRefundSaveAfter implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var bool
     */
    private $isRefund = false;

    /**
     * @var TransactionRepository
     */
    protected $transactionRepository;

    /**
     * CreditmemoRefundSaveAfter constructor.
     *
     * @param HelperData $helperData
     * @param RequestInterface $request
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(
        HelperData $helperData,
        RequestInterface $request,
        TransactionRepository $transactionRepository
    ) {
        $this->helperData            = $helperData;
        $this->request               = $request;
        $this->transactionRepository = $transactionRepository;

    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /* @var $creditmemo Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order      = $creditmemo->getOrder();
        $data       = $this->request->getPost('creditmemo');
        if ($creditmemo->getOrder()->getCustomerId()) {
            $account = $this->helperData->getAccountHelper()->create($creditmemo->getOrder()->getCustomerId());
            if (!$this->isRefund) {
                $this->isRefund = true;
                $pointAmount    = $creditmemo->getMpRewardEarn();
                if ($account->getPointBalance() < $creditmemo->getMpRewardEarn()) {
                    $pointAmount = $account->getPointBalance();
                }

                if ($pointAmount > 0 && $this->checkStatusTransaction($order->getId())) {
                    $this->helperData->addTransaction(
                        HelperData::ACTION_EARNING_REFUND,
                        $creditmemo->getOrder()->getCustomerId(),
                        -$pointAmount,
                        $creditmemo->getOrder()
                    );

                    $earnedPoints = abs($order->getMpRewardEarn() - $pointAmount);
                    $order->setMpRewardEarn($earnedPoints)->save();
                }

                if ($creditmemo->getMpRewardSpent() > 0
                    && isset($data['is_refund_point'])
                    && (int) $data['refund_point'] > 0
                ) {
                    $spendPoint = min($data['refund_point'], $creditmemo->getMpRewardSpent());

                    if ($data['refund_point'] === $spendPoint) {
                        $orderPoint = abs($order->getMpRewardSpent() - $spendPoint);
                        $order->setMpRewardSpent($orderPoint)->save();
                        $invoice = $creditmemo->getInvoice();

                        if ($invoice) {
                            $invoice->setMpRewardSpent($orderPoint)->save();
                        }
                        $creditmemo->setMpRewardSpent($spendPoint)->save();
                    }

                    $this->helperData->addTransaction(
                        HelperData::ACTION_SPENDING_REFUND,
                        $creditmemo->getOrder()->getCustomerId(),
                        $spendPoint,
                        $creditmemo->getOrder()
                    );
                }
                $baseSubtotalInvoiced = round($creditmemo->getOrder()->getBaseSubtotalInvoiced(), 2);
                $baseSubtotalRefunded = round($creditmemo->getOrder()->getBaseSubtotalRefunded(), 2);
                if ($baseSubtotalInvoiced === $baseSubtotalRefunded) {
                    $order->setState(Order::STATE_CLOSED)
                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
                }
            }
        }
    }

    /**
     * @param int $orderId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function checkStatusTransaction($orderId)
    {
        $transactions = $this->transactionRepository->getTransactionByOrderId($orderId)->getItems();
        if (count($transactions) === 0) {
            return false;
        }
        foreach ($transactions as $transaction) {
            if ((int) $transaction->getStatus() === Status::CANCELED) {
                return false;
            }
        }

        return true;
    }
}
