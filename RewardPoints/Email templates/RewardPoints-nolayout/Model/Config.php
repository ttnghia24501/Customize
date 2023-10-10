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

namespace Mageplaza\RewardPoints\Model;

use Magento\Framework\DataObject;
use Mageplaza\RewardPoints\Api\Data\ConfigInterface;

/**
 * Class Config
 * @package Mageplaza\RewardPoints\Model
 */
class Config extends DataObject implements ConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function setGeneral($value)
    {
        $this->setData(self::GENERAL, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGeneral()
    {
        return $this->getData(self::GENERAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEarning($value)
    {
        $this->setData(self::EARNING, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEarning()
    {
        return $this->getData(self::EARNING);
    }

    /**
     * {@inheritdoc}
     */
    public function setSpending($value)
    {
        $this->setData(self::SPENDING, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpending()
    {
        return $this->getData(self::SPENDING);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplay($value)
    {
        $this->setData(self::DISPLAY, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplay()
    {
        return $this->getData(self::DISPLAY);
    }
}
