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

namespace Mageplaza\RewardPoints\Api\Data;

/**
 * Interface RewardCustomerInterface
 * @package Mageplaza\RewardPoints\Api\Data
 */
interface RewardCustomerInterface
{
    const REWARD_ID           = 'reward_id';
    const IS_ACTIVE           = 'is_active';
    const CUSTOMER_ID         = 'customer_id';
    const POINT_BALANCE       = 'point_balance';
    const POINT_SPENT         = 'point_spent';
    const POINT_EARNED        = 'point_earned';
    const NOTIFICATION_UPDATE = 'notification_update';
    const NOTIFICATION_EXPIRE = 'notification_expire';

    /**
     * @return int
     */
    public function getRewardId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRewardId($value);

    /**
     * @return boolean
     */
    public function getIsActive();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * @return int
     */
    public function getPointBalance();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointBalance($value);

    /**
     * @return int
     */
    public function getPointSpent();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointSpent($value);

    /**
     * @return int
     */
    public function getPointEarned();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointEarned($value);

    /**
     * @return string
     */
    public function getNotificationUpdate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNotificationUpdate($value);

    /**
     * @return string
     */
    public function getNotificationExpire();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNotificationExpire($value);
}
