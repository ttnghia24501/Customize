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
 * @package     Mageplaza_RewardsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Api\Data;

/**
 * Interface OrderItemInterface
 * @api
 */
interface OrderItemInterface extends RewardInterface
{
    const MP_REWARD_SELL_POINTS   = 'mp_reward_sell_points';
    const MP_REWARD_REFERRAL_EARN = 'mp_reward_referral_earn';

    /**
     * @return int
     */
    public function getMpRewardReferralEarn();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpRewardReferralEarn($value);

    /**
     * @return int
     */
    public function getMpRewardSellPoints();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpRewardSellPoints($value);
}
