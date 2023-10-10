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
 * Interface OrderInterface
 * @api
 */
interface OrderInterface extends RewardInterface
{
    const MP_REWARD_SHIPPING_EARN          = 'mp_reward_shipping_earn';
    const MP_REWARD_SHIPPING_SPENT         = 'mp_reward_shipping_spent';
    const MP_REWARD_SHIPPING_DISCOUNT      = 'mp_reward_shipping_discount';
    const MP_REWARD_SHIPPING_BASE_DISCOUNT = 'mp_reward_shipping_base_discount';
    const MP_REWARD_EARN_AFTER_INVOICE     = 'mp_reward_earn_after_invoice';
    const MP_REWARD_REFERRAL_EARN          = 'mp_reward_referral_earn';
    const MP_REWARD_REFERRAL_ID            = 'mp_reward_referral_id';

    /**
     * @return int
     */
    public function getMpRewardShippingEarn();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMpRewardShippingEarn($value);

    /**
     * @return int
     */
    public function getMpRewardShippingSpent();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpRewardShippingSpent($value);

    /**
     * @return float
     */
    public function getMpRewardShippingDiscount();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setMpRewardShippingDiscount($value);

    /**
     * @return float
     */
    public function getMpRewardShippingBaseDiscount();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setMpRewardShippingBaseDiscount($value);

    /**
     * @return int
     */
    public function getMpRewardEarnAfterInvoice();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpRewardEarnAfterInvoice($value);

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
    public function getMpRewardReferralId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpRewardReferralId($value);
}
