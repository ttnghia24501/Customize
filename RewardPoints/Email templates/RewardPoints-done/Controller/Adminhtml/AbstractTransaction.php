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

namespace Mageplaza\RewardPoints\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\Transaction;
use Mageplaza\RewardPoints\Model\TransactionFactory;

/**
 * Class AbstractTransaction
 * @package Mageplaza\RewardPoints\Controller\Adminhtml
 */
abstract class AbstractTransaction extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mageplaza_RewardPoints::transaction';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * AbstractTransaction constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Filter $filter
     * @param HelperData $helperData
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Filter $filter,
        HelperData $helperData,
        TransactionFactory $transactionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->filter = $filter;
        $this->helperData = $helperData;
        $this->transactionFactory = $transactionFactory;

        parent::__construct($context);
    }

    /**
     * Initialize transaction object
     * @return Transaction
     */
    protected function _initTransaction()
    {
        $transactionId = $this->getRequest()->getParam('id', 0);
        $transaction = $this->transactionFactory->create();
        if ($transactionId) {
            $transaction->load($transactionId);
        }
        $this->registry->register('transaction', $transaction);

        return $transaction;
    }

    /**
     * Initialize reward customer object
     *
     * @param $customerId
     *
     * @return mixed
     */
    protected function _initRewardCustomer($customerId)
    {
        $account = $this->helperData->getAccountHelper()->getByCustomerId($customerId);
        $this->registry->register('reward_customer', $account);

        return $account;
    }

    /**
     * @param $status
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function massTransaction($status)
    {
        $collection = $this->filter->getCollection($this->transactionFactory->create()->getCollection());
        $count = 0;

        /** @var Transaction $transaction */
        foreach ($collection->getItems() as $transaction) {
            if (!$this->canProcess($transaction)) {
                continue;
            }
            try {
                if ($status == Status::CANCELED && $transaction->getStatus() != Status::CANCELED) {
                    $transaction->cancel();
                    $count++;
                } elseif ($status == Status::EXPIRED && $transaction->getStatus() != Status::EXPIRED) {
                    $transaction->expire();
                    $count++;
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while updating the transaction.' . $e->getMessage()));
            }
        }

        if ($count) {
            $this->messageManager->addSuccessMessage(
                $status == Status::CANCELED
                    ? __('A total of %1 record(s) have been cancelled.', $count)
                    : __('A total of %1 record(s) have been completed.', $count)
            );
        } else {
            $this->messageManager->addNoticeMessage(__('No transaction was updated.'));
        }

        return $this;
    }

    /***
     * Function to check transaction on RewardUltimate
     *
     * @param $transaction
     *
     * @return bool
     */
    public function canProcess($transaction)
    {
        return true;
    }
}
