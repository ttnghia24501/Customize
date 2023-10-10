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

namespace Mageplaza\RewardPoints\Api\Data\Config;

/**
 * Interface DisplayInterface
 * @package Mageplaza\RewardPoints\Api\Data\Config
 */
interface DisplayInterface
{
    const TOP_PAGE           = 'top_page';
    const MINI_CART          = 'mini_cart';

    /**
     * @return boolean
     */
    public function getTopPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTopPage($value);

    /**
     * @return boolean
     */
    public function getMiniCart();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMiniCart($value);
}
