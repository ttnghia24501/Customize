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
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Model\Config\Source;

use Mageplaza\ProductFeed\Model\Config\AbstractSource;

/**
 * Class ProductPath
 * @package Mageplaza\ProductFeed\Model\Config\Source
 */
class ProductPath extends AbstractSource
{
    const DEFAULT = 'default';
    const LONG    = 'long';
    const SHORT   = 'short';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::DEFAULT => __('Default'),
            self::LONG    => __('Longest Product Path'),
            self::SHORT   => __('Shortest Product Path'),
        ];
    }
}
