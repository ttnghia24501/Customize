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

namespace Mageplaza\RewardPoints\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Totals;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Total
 * @package Mageplaza\RewardPoints\Block\Sales\Order
 */
class Total extends Template
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Total constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Init Totals
     */
    public function initTotals()
    {
        /** @var Totals $totalsBlock */
        $totalsBlock = $this->getParentBlock();
        $source = $totalsBlock->getSource();

        if ($source) {
            if ($source->getMpRewardEarn() > 0) {
                $totalsBlock->addTotalBefore(new DataObject([
                    'code' => 'mp_earn',
                    'field' => 'mp_earn',
                    'label' => __('Earned'),
                    'value' => $this->helperData->getPointHelper()->format(
                        intval($source->getMpRewardEarn()),
                        false
                    ),
                    'is_formated' => true,
                ]), 'subtotal');
            }
            if ($source->getMpRewardSpent() > 0) {
                $totalsBlock->addTotalBefore(new DataObject([
                    'code' => 'mp_spent',
                    'field' => 'mp_spent',
                    'label' => __('Spent'),
                    'value' => $this->helperData->getPointHelper()->format(
                        intval($source->getMpRewardSpent()),
                        false
                    ),
                    'is_formated' => true,
                ]), 'subtotal');
            }
            if ($source->getMpRewardDiscount() > 0) {
                $totalsBlock->addTotal(new DataObject([
                    'code' => 'mp_reward_discount',
                    'field' => 'mp_reward_discount',
                    'label' => $this->helperData->getDiscountLabel(),
                    'value' => -$this->helperData->round($source->getMpRewardDiscount()),
                    'base_value' => -$this->helperData->round($source->getMpRewardBaseDiscount()),
                ]), 'subtotal');
            }
        }
    }
}
