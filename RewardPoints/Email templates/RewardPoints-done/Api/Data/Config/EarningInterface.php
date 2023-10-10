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
 * Interface EarningInterface
 * @package Mageplaza\RewardPoints\Api\Data\Config
 */
interface EarningInterface
{
    const ROUND_METHOD  = 'round_method';
    const EARN_FROM_TAX = 'earn_from';
    const EARN_SHIPPING = 'earn_shipping';
    const POINT_REFUND  = 'point_refund';
    const SALES_EARN    = 'sales_earn';
    /**
     * @return string
     */
    public function getRoundMethod();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setRoundMethod($value);

    /**
     * @return boolean
     */
    public function getEarnFromTax();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setEarnFromTax($value);

    /**
     * @return boolean
     */
    public function getEarnShipping();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setEarnShipping($value);

    /**
     * @return boolean
     */
    public function getPointRefund();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPointRefund($value);

    /**
     * @return \Mageplaza\RewardPoints\Api\Data\Config\SaleEarningInterface
     */
    public function getSalesEarn();

    /**
     * @param \Mageplaza\RewardPoints\Api\Data\Config\SaleEarningInterface $value
     *
     * @return $this
     */
    public function setSalesEarn($value);
}
