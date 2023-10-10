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

namespace Mageplaza\RewardPoints\Model\Config;

use Magento\Framework\DataObject;
use Mageplaza\RewardPoints\Api\Data\Config\SpendingInterface;

/**
 * Class Spending
 * @package Mageplaza\RewardPoints\Model\Config
 */
class Spending extends DataObject implements SpendingInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDiscountLabel()
    {
        return $this->getData(self::DISCOUNT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountLabel($value)
    {
        return $this->setData(self::DISCOUNT_LABEL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinimumBalance()
    {
        return $this->getData(self::MINIMUM_BALANCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinimumBalance($value)
    {
        return $this->setData(self::MINIMUM_BALANCE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumPointPerOrder()
    {
        return $this->getData(self::MAXIMUM_POINT_PER_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaximumPointPerOrder($value)
    {
        return $this->setData(self::MAXIMUM_POINT_PER_ORDER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpendOnTax()
    {
        return $this->getData(self::SPEND_ON_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setSpendOnTax($value)
    {
        return $this->setData(self::SPEND_ON_TAX, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpendOnShip()
    {
        return $this->getData(self::SPEND_ON_SHIP);
    }

    /**
     * {@inheritdoc}
     */
    public function setSpendOnShip($value)
    {
        return $this->setData(self::SPEND_ON_SHIP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getRestorePointAfterRefund()
    {
        return $this->getData(self::RESTORE_POINT_AFTER_REFUND);
    }

    /**
     * {@inheritdoc}
     */
    public function setRestorePointAfterRefund($value)
    {
        return $this->setData(self::RESTORE_POINT_AFTER_REFUND, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUseMaxPoint()
    {
        return $this->getData(self::USE_MAX_POINT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUseMaxPoint($value)
    {
        return $this->setData(self::USE_MAX_POINT, $value);
    }
}
