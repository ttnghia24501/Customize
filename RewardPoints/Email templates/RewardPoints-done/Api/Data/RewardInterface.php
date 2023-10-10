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
 * Interface RewardInterface
 * @package Mageplaza\RewardPoints\Api\Data
 */
interface RewardInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const MP_REWARD_EARN          = 'mp_reward_earn';
    const MP_REWARD_SPENT         = 'mp_reward_spent';
    const MP_REWARD_BASE_DISCOUNT = 'mp_reward_base_discount';
    const MP_REWARD_DISCOUNT      = 'mp_reward_discount';

    /**
     * @return int
     */
    public function getMpRewardEarn();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpRewardEarn($value);

    /**
     * @return int
     */
    public function getMpRewardSpent();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpRewardSpent($value);

    /**
     * @return float
     */
    public function getMpRewardBaseDiscount();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setMpRewardBaseDiscount($value);

    /**
     * @return float
     */
    public function getMpRewardDiscount();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setMpRewardDiscount($value);
}
