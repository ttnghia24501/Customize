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
 * Class Events
 * @package Mageplaza\ProductFeed\Model\Config\Source
 */
class Events extends AbstractSource
{
    const GENERATE_SUCCESS = '0';
    const GENERATE_ERROR = '1';
    const DELIVERY_SUCCESS = '2';
    const DELIVERY_ERROR = '3';
    const DELIVERY_DISABLED = '4';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::GENERATE_SUCCESS => __('Generate Successfully'),
            self::GENERATE_ERROR => __('Generate Error'),
            self::DELIVERY_SUCCESS => __('Delivery Successfully'),
            self::DELIVERY_ERROR => __('Delivery Error')
        ];
    }
}
