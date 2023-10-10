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
 * Class FieldsSeparate
 * @package Mageplaza\ProductFeed\Model\Config\Source
 */
class FieldsSeparate extends AbstractSource
{
    const TAB = 'tab';
    const COMMA = 'comma';
    const SEMICOLON = 'semi-colon';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::TAB => __('Tab'),
            self::COMMA => __('Comma'),
            self::SEMICOLON => __('Semi-colon'),
        ];
    }
}
