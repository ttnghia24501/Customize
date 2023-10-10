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

use Magento\Sales\Model\Order as SalesOrder;
use Mageplaza\RewardPoints\Api\Data\OrderInterface;

/**
 * Class Order
 * @package Mageplaza\RewardPoints\Model
 */
class Order extends SalesOrder implements OrderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMpRewardEarn()
    {
        return $this->getData(self::MP_REWARD_EARN);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardEarn($value)
    {
        return $this->setData(self::MP_REWARD_EARN, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardSpent()
    {
        return $this->getData(self::MP_REWARD_SPENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardSpent($value)
    {
        return $this->setData(self::MP_REWARD_SPENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardBaseDiscount()
    {
        return $this->getData(self::MP_REWARD_BASE_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardBaseDiscount($value)
    {
        return $this->setData(self::MP_REWARD_BASE_DISCOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardDiscount()
    {
        return $this->getData(self::MP_REWARD_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardDiscount($value)
    {
        return $this->setData(self::MP_REWARD_DISCOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardShippingSpent()
    {
        return $this->getData(self::MP_REWARD_SHIPPING_SPENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardShippingSpent($value)
    {
        return $this->setData(self::MP_REWARD_SHIPPING_SPENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardShippingEarn()
    {
        return $this->getData(self::MP_REWARD_SHIPPING_EARN);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardShippingEarn($value)
    {
        return $this->setData(self::MP_REWARD_SHIPPING_EARN, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardShippingDiscount()
    {
        return $this->getData(self::MP_REWARD_SHIPPING_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardShippingDiscount($value)
    {
        return $this->setData(self::MP_REWARD_SHIPPING_DISCOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardShippingBaseDiscount()
    {
        return $this->getData(self::MP_REWARD_SHIPPING_BASE_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardShippingBaseDiscount($value)
    {
        return $this->setData(self::MP_REWARD_SHIPPING_BASE_DISCOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardEarnAfterInvoice()
    {
        return $this->getData(self::MP_REWARD_EARN_AFTER_INVOICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardEarnAfterInvoice($value)
    {
        return $this->setData(self::MP_REWARD_EARN_AFTER_INVOICE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardReferralEarn()
    {
        return $this->getData(self::MP_REWARD_REFERRAL_EARN);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardReferralEarn($value)
    {
        return $this->setData(self::MP_REWARD_REFERRAL_EARN, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpRewardReferralId()
    {
        return $this->getData(self::MP_REWARD_REFERRAL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardReferralId($value)
    {
        return $this->setData(self::MP_REWARD_REFERRAL_ID, $value);
    }
}
