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
interface ConfigInterface
{
    const GENERAL  = 'general';
    const EARNING  = 'earning';
    const SPENDING = 'spending';
    const DISPLAY  = 'display';

    /**
     * @return \Mageplaza\RewardPoints\Api\Data\Config\GeneralInterface
     */
    public function getGeneral();

    /**
     * @param \Mageplaza\RewardPoints\Api\Data\Config\GeneralInterface $value
     *
     * @return $this
     */
    public function setGeneral($value);

    /**
     * @return \Mageplaza\RewardPoints\Api\Data\Config\EarningInterface
     */
    public function getEarning();

    /**
     * @param \Mageplaza\RewardPoints\Api\Data\Config\EarningInterface $value
     *
     * @return $this
     */
    public function setEarning($value);

    /**
     * @return \Mageplaza\RewardPoints\Api\Data\Config\SpendingInterface
     */
    public function getSpending();

    /**
     * @param \Mageplaza\RewardPoints\Api\Data\Config\SpendingInterface $value
     *
     * @return $this
     */
    public function setSpending($value);

    /**
     * @return \Mageplaza\RewardPoints\Api\Data\Config\DisplayInterface
     */
    public function getDisplay();

    /**
     * @param \Mageplaza\RewardPoints\Api\Data\Config\DisplayInterface $value
     *
     * @return $this
     */
    public function setDisplay($value);
}
