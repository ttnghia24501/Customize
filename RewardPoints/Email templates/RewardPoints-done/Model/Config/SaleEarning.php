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

namespace Mageplaza\RewardPoints\Model\Config;

use Magento\Framework\DataObject;
use Mageplaza\RewardPoints\Api\Data\Config\SaleEarningInterface;

/**
 * Class SaleEarning
 * @package Mageplaza\RewardPoints\Model\Config
 */
class SaleEarning extends DataObject implements SaleEarningInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEarnPointAfterInvoiceCreated()
    {
        return $this->getData(self::EARN_POINT_AFTER_INVOICE_CREATED);
    }

    /**
     * {@inheritdoc}
     */
    public function setEarnPointAfterInvoiceCreated($value)
    {
        return $this->setData(self::EARN_POINT_AFTER_INVOICE_CREATED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointExpired()
    {
        return $this->getData(self::POINT_EXPIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointExpired($value)
    {
        return $this->setData(self::POINT_EXPIRED, $value);
    }
}
