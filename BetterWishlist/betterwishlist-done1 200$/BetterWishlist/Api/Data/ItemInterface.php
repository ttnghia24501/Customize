<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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

namespace Mageplaza\BetterWishlist\Api\Data;

/**
 * Interface ItemInterface
 * @package Mageplaza\BetterWishlist\Api\Data
 */
interface ItemInterface
{
    const WISHLIST_ITEM_ID = 'wishlist_item_id';
    const PRODUCT_ID       = 'product_id';
    const STORE_ID         = 'store_id';
    const ADDED_AT         = 'added_at';
    const DESCRIPTION      = 'description';
    const QTY              = 'qty';

    /**
     * @return int
     */
    public function getWishlistItemId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setWishlistItemId($value);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setProductId($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return string
     */
    public function getAddedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAddedAt($value);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDescription($value);

    /**
     * @return int
     */
    public function getQty();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setQty($value);
}
