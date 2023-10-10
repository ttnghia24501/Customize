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

namespace Mageplaza\RewardPoints\Api\Data\Config;

/**
 * Interface SpendingInterface
 * @package Mageplaza\RewardPoints\Api\Data\Config
 */
interface SpendingInterface
{
    const DISCOUNT_LABEL             = 'discount_label';
    const MINIMUM_BALANCE            = 'minimum_balance';
    const MAXIMUM_POINT_PER_ORDER    = 'maximum_point_per_order';
    const SPEND_ON_TAX               = 'spend_on_tax';
    const SPEND_ON_SHIP              = 'spend_on_ship';
    const RESTORE_POINT_AFTER_REFUND = 'restore_point_after_refund';
    const USE_MAX_POINT              = 'use_max_point';

    /**
     * @return string
     */
    public function getDiscountLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDiscountLabel($value);

    /**
     * @return int
     */
    public function getMinimumBalance();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMinimumBalance($value);

    /**
     * @return int
     */
    public function getMaximumPointPerOrder();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMaximumPointPerOrder($value);

    /**
     * @return boolean
     */
    public function getSpendOnTax();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSpendOnTax($value);

    /**
     * @return boolean
     */
    public function getSpendOnShip();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSpendOnShip($value);

    /**
     * @return boolean
     */
    public function getRestorePointAfterRefund();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setRestorePointAfterRefund($value);

    /**
     * @return boolean
     */
    public function getUseMaxPoint();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setUseMaxPoint($value);
}
