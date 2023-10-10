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

/**
 * Interface RewardCustomerRepositoryInterface
 * @api
 */
interface RewardCustomerRepositoryInterface
{
    /**
     * Lists Reward customer that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\RewardPoints\Api\Data\RewardCustomerSearchResultInterface Reward customer search
     *     result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * @param int $customerId
     * @param bool $isUpdate
     * @param bool $isExpire
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function subscribe($customerId, $isUpdate, $isExpire);

    /**
     * @param int $customerId
     *
     * @return \Mageplaza\RewardPoints\Api\Data\RewardCustomerInterface
     */
    public function getAccountByCustomerId($customerId);

    /**
     * @param int $id
     *
     * @return \Mageplaza\RewardPoints\Api\Data\RewardCustomerInterface
     */
    public function getAccountById($id);

    /**
     * @return mixed
     */
    public function count();

    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteAccountById($id);

    /**
     * @param string $email
     *
     * @return \Mageplaza\RewardPoints\Api\Data\RewardCustomerInterface
     */
    public function getAccountByEmail($email);
}
