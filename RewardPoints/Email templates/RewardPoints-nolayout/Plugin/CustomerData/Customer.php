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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Plugin\CustomerData;

use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Customer
 * @package Mageplaza\RewardPoints\Plugin\CustomerData
 */
class Customer
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Cart constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        if ($this->helperData->isDisplayPointOnTopLink()) {
            $result['point_balance'] = $this->helperData->getAccountHelper()->get()->getBalanceFormatted();
        }

        return $result;
    }
}
