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
use Mageplaza\RewardPoints\Api\Data\Config\DisplayInterface;

/**
 * Class Display
 * @package Mageplaza\RewardPoints\Model\Config
 */
class Display extends DataObject implements DisplayInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTopPage()
    {
        return $this->getData(self::TOP_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTopPage($value)
    {
        return $this->setData(self::TOP_PAGE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMiniCart()
    {
        return $this->getData(self::MINI_CART);
    }

    /**
     * {@inheritdoc}
     */
    public function setMiniCart($value)
    {
        return $this->setData(self::MINI_CART, $value);
    }
}
