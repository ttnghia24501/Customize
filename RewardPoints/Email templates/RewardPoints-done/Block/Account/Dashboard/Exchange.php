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

namespace Mageplaza\RewardPoints\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPoints\Block\Account\Dashboard;
use Mageplaza\RewardPoints\Model\Rate;
use Mageplaza\RewardPoints\Model\Source\ActionType;

/**
 * Class Exchange
 * @package Mageplaza\RewardPoints\Block\Account\Dashboard
 */
class Exchange extends Dashboard
{
    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canDisplay()
    {
        return $this->getEarningRate() || $this->getSpendingRate()
            || $this->getMaxPointPerCustomer() || $this->getPointExpired();
    }

    /**
     * Get max point per customer
     * @return int
     */
    public function getMaxPointPerCustomer()
    {
        return $this->helper->getMaxPointPerCustomer();
    }

    /**
     * Get point expired
     * @return mixed
     */
    public function getPointExpired()
    {
        $expired = $this->helper->getSalesPointExpiredAfter();
        if (empty($expired)) {
            return false;
        }

        return $expired > 1 ? __('%1 days', $expired) : __('%1 day', $expired);
    }

    /**
     * Get the earning rate
     *
     * @return Rate|null
     * @throws NoSuchEntityException
     */
    public function getEarningRate()
    {
        return $this->getRate(ActionType::EARNING);
    }

    /**
     * Get the spending rate
     *
     * @return Rate|null
     * @throws NoSuchEntityException
     */
    public function getSpendingRate()
    {
        return $this->getRate(ActionType::SPENDING);
    }

    /**
     * @param string $type
     *
     * @return Rate|null
     * @throws NoSuchEntityException
     */
    public function getRate($type)
    {
        if ($type === ActionType::EARNING) {
            $rate = $this->helper->getCalculationHelper()->getEarningRate();
        } else {
            $rate = $this->helper->getCalculationHelper()->getSpendingRate();
        }

        if (!$rate->isValid()) {
            return null;
        }

        return $rate;
    }
}
