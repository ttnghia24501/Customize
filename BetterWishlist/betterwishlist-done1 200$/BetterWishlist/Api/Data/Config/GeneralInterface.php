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

namespace Mageplaza\BetterWishlist\Api\Data\Config;

/**
 * Interface GeneralInterface
 * @package Mageplaza\BetterWishlist\Api\Data\Config
 */
interface GeneralInterface
{
    const ENABLED                        = 'enabled';
    const REMOVE_AFTER_ADD_TO_CART       = 'remove_after_add_to_cart';
    const ENABLED_MULTI_WISHLIST         = 'enabled_multi_wishlist';
    const SHOW_ALL_ITEM                  = 'show_all_item';
    const ALLOW_CUSTOMER_CREATE_WISHLIST = 'allow_customer_create_wishlist';
    const LIMIT_NUMBER_OF_WISHLIST       = 'limit_number_of_wishlist';
    const FONT_AWESOME                   = 'font_awesome';

    /**
     * @return boolean
     */
    public function getEnabled();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setEnabled($value);

    /**
     * @return boolean
     */
    public function getRemoveAfterAddToCart();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setRemoveAfterAddToCart($value);

    /**
     * @return boolean
     */
    public function getEnabledMultiWishlist();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setEnabledMultiWishlist($value);

    /**
     * @return boolean
     */
    public function getShowAllItem();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setShowAllItem($value);

    /**
     * @return boolean
     */
    public function getAllowCustomerCreateWishlist();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setAllowCustomerCreateWishlist($value);

    /**
     * @return int
     */
    public function getLimitNumberOfWishlist();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setLimitNumberOfWishlist($value);

    /**
     * @return boolean
     */
    public function getFontAwesome();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setFontAwesome($value);
}
