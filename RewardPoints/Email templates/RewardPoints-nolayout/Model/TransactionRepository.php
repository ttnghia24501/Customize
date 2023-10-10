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

namespace Mageplaza\RewardPoints\Model;

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPoints\Api\Data\TransactionInterface;
use Mageplaza\RewardPoints\Api\Data\TransactionSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPoints\Api\TransactionRepositoryInterface;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Source\Status;

/**
 * Class TransactionRepository
 * @package Mageplaza\RewardPoints\Model
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * TransactionRepository constructor.
     *
     * @param Data $helperData
     * @param TransactionFactory $transactionFactory
     * @param CustomerFactory $customerFactory
     * @param SearchResultFactory $searchResultFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        Data $helperData,
        TransactionFactory $transactionFactory,
        CustomerFactory $customerFactory,
        SearchResultFactory $searchResultFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->transactionFactory    = $transactionFactory;
        $this->customerFactory       = $customerFactory;
        $this->searchResultFactory   = $searchResultFactory;
        $this->helperData            = $helperData;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor   = $collectionProcessor;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        $searchResult = $this->searchResultFactory->create();

        $this->collectionProcessor->process($searchCriteria, $searchResult);

        return $searchResult;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getTransactionByCustomerId($customerId)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        return $this->searchResultFactory->create()->addFieldToFilter('customer_id', $customerId);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getListByCustomerId(SearchCriteriaInterface $searchCriteria, $customerId)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $searchResult = $this->searchResultFactory->create()->addFieldToFilter('customer_id', $customerId);

        $this->collectionProcessor->process($searchCriteria, $searchResult);

        return $searchResult;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getTransactionByAccountId($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        return $this->searchResultFactory->create()->addFieldToFilter('reward_id', $id);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getTransactionByOrderId($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        return $this->searchResultFactory->create()->addFieldToFilter('order_id', $id);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function count()
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $total = ['total' => $this->searchResultFactory->create()->getTotalCount()];

        return [$total];
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function expire($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        try {
            $transaction = $this->getTransactionById($id);
            $status      = (int) $transaction->getStatus();
            if ($status === Status::EXPIRED) {
                throw new InputException(__('Transaction has been expired.'));
            }

            $transaction->expire();
            $status = (int) $transaction->getStatus();
            if ($status !== Status::EXPIRED) {
                throw new CouldNotSaveException((__('Could not expire the transaction ')));
            }

        } catch (Exception $e) {
            throw new CouldNotSaveException(
                (__('Something went wrong while processing the transaction. Details : %1', $e->getMessage()))
            );
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function cancel($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        try {
            $transaction = $this->getTransactionById($id);
            if ((int) $transaction->getStatus() === Status::CANCELED) {
                throw new InputException(__('Transaction has been canceled.'));
            }

            if ($this->isActionImport($transaction)) {
                throw new InputException(__('Can\'t cancel transaction import'));
            }
            $transaction->cancel();
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not cancel the transaction. Details: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function save(TransactionInterface $data)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        if (!$data->getCustomerId()) {
            throw new InputException(__('Customer id required'));
        }

        if (!$data->getPointAmount()) {
            throw new InputException(__('Point amount required'));
        }

        $customer = $this->customerFactory->create()->load($data->getCustomerId());
        if (!$customer->getId()) {
            throw new NoSuchEntityException(__('Customer doesn\'t exist'));
        }

        try {
            $transaction = $this->transactionFactory->create();
            $transaction->createTransaction(
                Data::ACTION_ADMIN,
                $customer,
                new DataObject([
                    'point_amount' => $data->getPointAmount(),
                    'comment'      => $data->getComment(),
                    'expire_after' => $data->getExpireAfter()
                ])
            );
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getTransactionById($id)
    {
        if (!$id) {
            throw new InputException(__('Transaction id required'));
        }

        $transaction = $this->transactionFactory->create()->load($id);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Transaction id doesn\'t exist'));
        }

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isActionImport($transaction)
    {
        return $transaction->getActionCode() === Data::ACTION_IMPORT_TRANSACTION;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getListByOrderId(SearchCriteriaInterface $searchCriteria, $orderId)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $searchResult = $this->searchResultFactory->create()->addFieldToFilter('order_id', $orderId);

        $this->collectionProcessor->process($searchCriteria, $searchResult);

        return $searchResult;
    }
}
