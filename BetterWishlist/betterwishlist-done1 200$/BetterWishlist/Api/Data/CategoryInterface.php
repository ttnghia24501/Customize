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
 * Interface CategoryInterface
 * @package Mageplaza\BetterWishlist\Api\Data
 */
interface CategoryInterface
{
    const CATEGORY_ID   = 'category_id';
    const CATEGORY_NAME = 'category_name';
    const IS_DEFAULT    = 'is_default';
    const ITEMS         = 'items';

    /**
     * @return string
     */
    public function getCategoryId();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCategoryId($value);

    /**
     * @return string
     */
    public function getCategoryName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCategoryName($value);

    /**
     * @return boolean
     */
    public function getIsDefault();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setIsDefault($value);

    /**
     * @return \Mageplaza\BetterWishlist\Api\Data\ItemInterface[]
     */
    public function getItems();

    /**
     * @param \Mageplaza\BetterWishlist\Api\Data\ItemInterface[] $value
     *
     * @return $this
     */
    public function setItems($value);
}
