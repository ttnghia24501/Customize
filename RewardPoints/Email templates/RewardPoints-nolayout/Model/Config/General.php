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
use Mageplaza\RewardPoints\Api\Data\Config\GeneralInterface;

/**
 * Class General
 * @package Mageplaza\RewardPoints\Model\Config
 */
class General extends DataObject implements GeneralInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($value)
    {
        return $this->setData(self::ENABLED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountNavigationLabel()
    {
        return $this->getData(self::ACCOUNT_NAVIGATION_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setAccountNavigationLabel($value)
    {
        return $this->setData(self::ACCOUNT_NAVIGATION_LABEL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointLabel()
    {
        return $this->getData(self::POINT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointLabel($value)
    {
        return $this->setData(self::POINT_LABEL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralPointLabel()
    {
        return $this->getData(self::PLURAL_POINT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setPluralPointLabel($value)
    {
        return $this->setData(self::PLURAL_POINT_LABEL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayPointLabel()
    {
        return $this->getData(self::DISPLAY_POINT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayPointLabel($value)
    {
        return $this->setData(self::DISPLAY_POINT_LABEL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getZeroAmount()
    {
        return $this->getData(self::ZERO_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setZeroAmount($value)
    {
        return $this->setData(self::ZERO_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getShowPointIcon()
    {
        return $this->getData(self::SHOW_POINT_ICON);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowPointIcon($value)
    {
        return $this->setData(self::SHOW_POINT_ICON, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return $this->getData(self::ICON);
    }

    /**
     * {@inheritdoc}
     */
    public function setIcon($value)
    {
        return $this->setData(self::ICON, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumPoint()
    {
        return $this->getData(self::MAXIMUM_POINT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaximumPoint($value)
    {
        return $this->setData(self::MAXIMUM_POINT, $value);
    }
}
