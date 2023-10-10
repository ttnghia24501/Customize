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

namespace Mageplaza\BetterWishlist\Model\Api\Data;

use Magento\Framework\DataObject;
use Mageplaza\BetterWishlist\Api\Data\ItemInterface;

/**
 * Class Item
 * @package Mageplaza\BetterWishlist\Model\Api\Data
 */
class Item extends DataObject implements ItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getWishlistItemId()
    {
        return $this->getData(self::WISHLIST_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setWishlistItemId($value)
    {
        $this->setData(self::WISHLIST_ITEM_ID, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($value)
    {
        $this->setData(self::PRODUCT_ID, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($value)
    {
        $this->setData(self::STORE_ID, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddedAt()
    {
        return $this->getData(self::ADDED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddedAt($value)
    {
        $this->setData(self::ADDED_AT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION)?:'';
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        $this->setData(self::DESCRIPTION, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($value)
    {
        $this->setData(self::QTY, $value);

        return $this;
    }
}
