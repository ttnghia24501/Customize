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
 * Interface TransactionInterface
 * @package Mageplaza\RewardPoints\Api\Data
 */
interface TransactionInterface
{
    const TRANSACTION_ID    = 'transaction_id';
    const REWARD_ID         = 'reward_id';
    const CUSTOMER_ID       = 'customer_id';
    const ACTION_CODE       = 'action_code';
    const ACTION_TYPE       = 'action_type';
    const STORE_ID          = 'store_id';
    const POINT_AMOUNT      = 'point_amount';
    const POINT_REMAINING   = 'point_remaining';
    const POINT_USED        = 'point_used';
    const STATUS            = 'status';
    const ORDER_ID          = 'order_id';
    const CREATED_AT        = 'created_at';
    const EXPIRATION_DATE   = 'expiration_date';
    const EXPIRE_EMAIL_SENT = 'expire_email_sent';
    const EXTRA_CONTENT     = 'extra_content';
    const COMMENT           = 'comment';
    const EXPIRE_AFTER      = 'expire_after';

    /**
     * @return int
     */
    public function getTransactionId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setTransactionId($value);

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
     * @return string
     */
    public function getActionCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setActionCode($value);

    /**
     * @return string
     */
    public function getActionType();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setActionType($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return int
     */
    public function getPointAmount();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointAmount($value);

    /**
     * @return int
     */
    public function getPointRemaining();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointRemaining($value);

    /**
     * @return int
     */
    public function getPointUsed();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointUsed($value);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setOrderId($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * @return string
     */
    public function getExpirationDate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExpirationDate($value);

    /**
     * @return string
     */
    public function getExpireEmailSent();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExpireEmailSent($value);

    /**
     * @return string
     */
    public function getExtraContent();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExtraContent($value);

    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setComment($value);

    /**
     * @return int|null
     */
    public function getExpireAfter();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setExpireAfter($value);
}
