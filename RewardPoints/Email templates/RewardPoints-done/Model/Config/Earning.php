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
use Mageplaza\RewardPoints\Api\Data\Config\EarningInterface;

/**
 * Class Earning
 * @package Mageplaza\RewardPoints\Model\Config
 */
class Earning extends DataObject implements EarningInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRoundMethod()
    {
        return $this->getData(self::ROUND_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoundMethod($value)
    {
        return $this->setData(self::ROUND_METHOD, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getEarnFromTax()
    {
        return $this->getData(self::EARN_FROM_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setEarnFromTax($value)
    {
        return $this->setData(self::EARN_FROM_TAX, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getEarnShipping()
    {
        return $this->getData(self::EARN_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setEarnShipping($value)
    {
        return $this->setData(self::EARN_SHIPPING, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointRefund()
    {
        return $this->getData(self::POINT_REFUND);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointRefund($value)
    {
        return $this->setData(self::POINT_REFUND, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSalesEarn()
    {
        return $this->getData(self::SALES_EARN);
    }

    /**
     * {@inheritdoc}
     */
    public function setSalesEarn($value)
    {
        return $this->setData(self::SALES_EARN, $value);
    }
}
