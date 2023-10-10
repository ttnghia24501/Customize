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
 * Interface RewardRateInterface
 * @package Mageplaza\RewardPoints\Api\Data
 */
interface RewardRateInterface
{
    const RATE_ID            = 'rate_id';
    const POINTS             = 'points';
    const MONEY              = 'money';
    const PRIORITY           = 'priority';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const WEBSITE_IDS        = 'website_ids';
    const DIRECTION          = 'direction';

    /**
     * @return int
     */
    public function getRateId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRateId($value);

    /**
     * @return int
     */
    public function getPoints();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPoints($value);

    /**
     * @return float
     */
    public function getMoney();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setMoney($value);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPriority($value);

    /**
     * @return string
     */
    public function getCustomerGroupIds();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCustomerGroupIds($value);

    /**
     * @return string
     */
    public function getWebsiteIds();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setWebsiteIds($value);

    /**
     * @return int
     */
    public function getDirection();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setDirection($value);
}
