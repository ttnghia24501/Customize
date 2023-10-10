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
 * Interface EarningInterface
 * @package Mageplaza\RewardPoints\Api\Data\Config
 */
interface SaleEarningInterface
{
    const EARN_POINT_AFTER_INVOICE_CREATED = 'earn_point_after_invoice_created';
    const POINT_EXPIRED = 'point_expired';

    /**
     * @return string
     */
    public function getEarnPointAfterInvoiceCreated();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setEarnPointAfterInvoiceCreated($value);

    /**
     * @return mixed
     */
    public function getPointExpired();

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setPointExpired($value);
}
