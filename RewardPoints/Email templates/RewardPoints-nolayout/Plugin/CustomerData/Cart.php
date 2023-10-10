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
 * Class Cart
 * @package Mageplaza\RewardPoints\Plugin\CustomerData
 */
class Cart
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
     * Add Reward point data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        if (!$this->helperData->isDisablePointOnMiniCart()) {
            $totals = $this->helperData->getQuote()->getTotals();
            $pointHelper = $this->helperData->getPointHelper();

            if (isset($totals['mp_reward_earn'])) {
                if ($this->helperData->isEarnWithSpent()
                    || (!$this->helperData->isEarnWithSpent() && !isset($totals['mp_reward_spent']))) {
                    $result['rewardEarn'] = $pointHelper->format($totals['mp_reward_earn']->getValue(), false);
                    $result['rewardIcon'] = $pointHelper->getIconHtml();
                }
            }
        }

        return $result;
    }
}
