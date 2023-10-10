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

namespace Mageplaza\BetterWishlist\Api;

use Magento\Framework\Exception\InputException;

/**
 * Class ConfigRepositoryInterface
 * @package Mageplaza\BetterWishlist\Api
 */
interface ConfigRepositoryInterface
{
    /**
     * @param string|null $storeId
     *
     * @return \Mageplaza\BetterWishlist\Api\Data\ConfigInterface
     *
     * @throws InputException
     */
    public function getConfigs($storeId = null);
}
