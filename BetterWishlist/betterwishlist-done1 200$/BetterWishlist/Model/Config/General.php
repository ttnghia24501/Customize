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
 * @package     Mageplaza_BetterWishlist
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Model\Config;

use Magento\Framework\DataObject;
use Mageplaza\BetterWishlist\Api\Data\Config\GeneralInterface;

/**
 * Class General
 * @package Mageplaza\BetterWishlist\Model\Config
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
        $this->setData(self::ENABLED, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemoveAfterAddToCart()
    {
        return $this->getData(self::REMOVE_AFTER_ADD_TO_CART);
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoveAfterAddToCart($value)
    {
        $this->setData(self::REMOVE_AFTER_ADD_TO_CART, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabledMultiWishlist()
    {
        return $this->getData(self::ENABLED_MULTI_WISHLIST);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabledMultiWishlist($value)
    {
        $this->setData(self::ENABLED_MULTI_WISHLIST, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowAllItem()
    {
        return $this->getData(self::SHOW_ALL_ITEM);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowAllItem($value)
    {
        $this->setData(self::SHOW_ALL_ITEM, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowCustomerCreateWishlist()
    {
        return $this->getData(self::ALLOW_CUSTOMER_CREATE_WISHLIST);
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowCustomerCreateWishlist($value)
    {
        $this->setData(self::ALLOW_CUSTOMER_CREATE_WISHLIST, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitNumberOfWishlist()
    {
        return $this->getData(self::LIMIT_NUMBER_OF_WISHLIST);
    }

    /**
     * {@inheritdoc}
     */
    public function setLimitNumberOfWishlist($value)
    {
        $this->setData(self::LIMIT_NUMBER_OF_WISHLIST, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFontAwesome()
    {
        return $this->getData(self::FONT_AWESOME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFontAwesome($value)
    {
        $this->setData(self::FONT_AWESOME, $value);

        return $this;
    }
}
