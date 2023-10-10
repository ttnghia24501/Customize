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
 * Interface InvoiceInterface
 * @api
 */
interface InvoiceInterface extends RewardInterface
{
    const MP_REWARD_SHIPPING_EARN          = 'mp_reward_shipping_earn';
    const MP_REWARD_SHIPPING_SPENT         = 'mp_reward_shipping_spent';
    const MP_REWARD_SHIPPING_DISCOUNT      = 'mp_reward_shipping_discount';
    const MP_REWARD_SHIPPING_BASE_DISCOUNT = 'mp_reward_shipping_base_discount';

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
}
