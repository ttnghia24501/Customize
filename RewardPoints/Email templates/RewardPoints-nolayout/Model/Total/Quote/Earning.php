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

namespace Mageplaza\RewardPoints\Model\Total\Quote;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\RewardPoints\Helper\Calculation;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Earning
 * @package Mageplaza\RewardPoints\Model\Total\Quote
 */
class Earning extends AbstractTotal
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var EventManager
     */
    protected $_eventManager;

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Earning constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param EventManager $eventManager
     * @param HelperData $helperData
     * @param RequestInterface $request
     * @param Calculation $calculation
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        EventManager $eventManager,
        HelperData $helperData,
        RequestInterface $request,
        Calculation $calculation
    ) {
        $this->helperData = $helperData;
        $this->priceCurrency = $priceCurrency;
        $this->_eventManager = $eventManager;
        $this->calculation = $calculation;
        $this->request = $request;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this|Earning
     * @throws NoSuchEntityException
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();
        if (!$items
            || in_array(
                $this->request->getFullActionName(),
                ['multishipping_checkout_overviewPost', 'multishipping_checkout_overview'],
                true
            )
        ) {
            return $this;
        }

        $storeId = $quote->getStoreId();
        $this->calculation->resetRewardData(
            $items,
            $quote,
            ['mp_reward_earn', 'mp_reward_shipping_earn'],
            ['mp_reward_earn']
        );
        if (!$this->helperData->isEnabled($storeId) || $total->getBaseGrandTotal() <= 0) {
            return $this;
        }

        if (!$this->helperData->isRewardAccountActive()) {
            return $this;
        }

        $this->_eventManager->dispatch('mpreward_earning_points_before', [
            'quote' => $quote,
            'items' => $items,
            'total' => $total,
            'shipping_assignment' => $shippingAssignment
        ]);

        $this->calculation->resetDeltaRoundPoint();

        $this->calculation->setData('total', $total);
        $totalPointEarn = 0;
        $lastItem = null;

        /** @var Item $item */
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var Item $child */
                foreach ($item->getChildren() as $child) {
                    $totalPointEarn += $this->calculation->calculateEarningPoints($child);
                    $lastItem = $child;
                }
            } elseif ($item instanceof Item) {
                $totalPointEarn += $this->calculation->calculateEarningPoints($item);
                $lastItem = $item;
            }
        }

        if ($this->helperData->isEarnPointFromShipping($storeId)) {
            $shippingEarn = $this->calculation->calculateShippingEarningPoints($quote, $shippingAssignment, $total);
            $shippingEarn = $this->helperData->getPointHelper()
                ->round($shippingEarn + $this->calculation->getDeltaRoundPoint('customer'));
            $quote->setMpRewardShippingEarn($shippingEarn);
            $totalPointEarn += $shippingEarn;
        } else {
            if ($lastItem) {
                $tmpPoint = $this->helperData->getPointHelper()
                    ->round($this->calculation->getDeltaRoundPoint('customer'));
                $lastItem->setMpRewardEarn($lastItem->getMpRewardEarn() + $tmpPoint);
                $totalPointEarn += $tmpPoint;
            }
        }

        $this->_eventManager->dispatch('mpreward_last_item_earning_points', [
            'quote' => $quote,
            'shipping_assignment' => $shippingAssignment,
            'total' => $total,
            'last_item' => $lastItem,
            'calculation' => $this->calculation
        ]);

        $quote->setMpRewardEarn($quote->getMpRewardEarn() + $totalPointEarn);

        $this->_eventManager->dispatch('mpreward_earning_points_after', [
            'quote' => $quote,
            'items' => $items,
            'shipping_assignment' => $shippingAssignment,
            'last_item' => $lastItem,
            'total' => $total
        ]);

        return $this;
    }

    /**
     * Retrieve reward total data and set it to quote address
     *
     * @param Quote $quote
     * @param Total $total
     *
     * @return array|null
     */
    public function fetch(Quote $quote, Total $total)
    {
        if ($this->helperData->isEnabled($quote->getStoreId())
            && $quote->getMpRewardEarn() > 0 && $this->helperData->isRewardAccountActive()
            && !in_array(
                $this->request->getFullActionName(),
                ['multishipping_checkout_overviewPost', 'multishipping_checkout_overview', 'paypal_express_review'],
                true
            )
        ) {
            $title = $quote->getCustomerId() ? __('You will earn') : __('Login to earn');

            return [
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $quote->getMpRewardEarn()
            ];
        }

        return [];
    }

}
