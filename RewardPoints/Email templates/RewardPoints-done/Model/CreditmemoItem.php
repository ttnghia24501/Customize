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

use Magento\Sales\Model\Order\Creditmemo\Item as SalesCreditmemoItem;
use Mageplaza\RewardPoints\Api\Data\CreditmemoItemInterface;

/**
 * Class OrderItem
 * @package Mageplaza\RewardPoints\Model
 */
class CreditmemoItem extends SalesCreditmemoItem implements CreditmemoItemInterface
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
    public function getMpRewardSellPoints()
    {
        return $this->getData(self::MP_REWARD_SELL_POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpRewardSellPoints($value)
    {
        return $this->setData(self::MP_REWARD_SELL_POINTS, $value);
    }
}
