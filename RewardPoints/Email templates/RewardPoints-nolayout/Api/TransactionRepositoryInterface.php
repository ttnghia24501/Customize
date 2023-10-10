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

namespace Mageplaza\RewardPoints\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Api\Data\TransactionInterface;
use Mageplaza\RewardPoints\Api\Data\TransactionSearchResultInterface;

/**
 * Interface TransactionRepositoryInterface
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Lists Transaction that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface Transaction search result
     *     interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * Lists Transaction by customer id that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @param int $customerId
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface Transaction search result
     *     interface.
     */
    public function getListByCustomerId(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $customerId);

    /**
     * @param int $customerId
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     */
    public function getTransactionByCustomerId($customerId);

    /**
     * @return mixed
     */
    public function count();

    /**
     * @param int $id
     *
     * @return \Mageplaza\RewardPoints\Api\Data\TransactionSearchResultInterface
     */
    public function getTransactionByAccountId($id);

    /**
     * @param int $id
     *
     * @return \Mageplaza\RewardPoints\Api\Data\TransactionSearchResultInterface
     */
    public function getTransactionByOrderId($id);

    /**
     * @param int $id
     *
     * @return bool
     */
    public function expire($id);

    /**
     * @param int $id
     *
     * @return bool
     */
    public function cancel($id);

    /**
     * Required(customer_id, point_amount)
     *
     * @param \Mageplaza\RewardPoints\Api\Data\TransactionInterface $data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Mageplaza\RewardPoints\Api\Data\TransactionInterface $data);
}
