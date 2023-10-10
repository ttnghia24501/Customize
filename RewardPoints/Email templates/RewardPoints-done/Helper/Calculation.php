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

namespace Mageplaza\RewardPoints\Helper;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Calculator;
use Magento\Framework\Math\CalculatorFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection as CreditmemoCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Model\Rate;
use Mageplaza\RewardPoints\Model\ResourceModel\Rate\Collection;
use Mageplaza\RewardPoints\Model\Source\Direction;
use Mageplaza\RewardPoints\Model\TransactionFactory;

/**
 * Class Calculation
 * @package Mageplaza\RewardPoints\Helper
 */
class Calculation extends Data
{
    /**
     * @var array Rates
     */
    protected $rateByDirection = [];

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var Calculator[]
     */
    protected $_calculators = [];

    /**
     * @var CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var array
     */
    protected $_deltaPoint = [];

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var null
     */
    protected $lastItemMathRule = null;

    /**
     * Calculation constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $timeZone
     * @param SessionFactory $sessionFactory
     * @param CalculatorFactory $calculatorFactory
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone,
        SessionFactory $sessionFactory,
        CalculatorFactory $calculatorFactory,
        TransactionFactory $transactionFactory
    ) {
        $this->_calculatorFactory = $calculatorFactory;
        $this->transactionFactory = $transactionFactory;

        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $priceCurrency,
            $timeZone,
            $sessionFactory
        );
    }

    /**
     * @param float $point
     * @param string $type
     *
     * @return float|int
     */
    public function deltaRoundPoint($point, $type)
    {
        $finalPoint = floor($point);
        if (!isset($this->_deltaPoint[$type])) {
            $this->_deltaPoint[$type] = 0.0;
        }

        $this->_deltaPoint[$type] += $point - $finalPoint;

        if ($this->_deltaPoint[$type] >= 0.5) {
            ++$finalPoint;
            $this->_deltaPoint[$type] = 0.0;
        }

        return $finalPoint;
    }

    /**
     * @param string $type
     *
     * @return float|int
     */
    public function getDeltaRoundPoint($type)
    {
        if (isset($this->_deltaPoint[$type])) {
            return $this->_deltaPoint[$type];
        }

        return 0;
    }

    /**
     * @param string $type
     *
     * @return array|float
     */
    public function resetDeltaRoundPoint($type = '')
    {
        if ($type) {
            return $this->_deltaPoint[$type] = 0.0;
        }

        return $this->_deltaPoint = [];
    }

    /**
     * @param CreditmemoCollection|InvoiceCollection|false $instanceCollection
     * @param array $fields
     * @param bool $isAddRewardShipping
     *
     * @return array
     */
    public function getOldRewardData($instanceCollection, $fields, &$isAddRewardShipping = false)
    {
        $rewardData = [];
        foreach ($instanceCollection as $collection) {
            if ($isAddRewardShipping && ($collection->getMpRewardDiscount() || $collection->getMpRewardEarn())) {
                $isAddRewardShipping = false;
            }

            foreach ($collection->getItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }

                foreach ($fields as $field) {
                    $itemId = $orderItem->getId();
                    if (!isset($rewardData[$itemId]) || !isset($rewardData[$itemId][$field])) {
                        $rewardData[$itemId][$field] = 0;
                    }
                    $rewardData[$itemId][$field] += $item->getData($field);
                }
            }
        }

        return $rewardData;
    }

    /********************************************** Earning Calculation ******************************************
     *
     * @param Item $item
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function calculateEarningPoints(Item $item)
    {
        $earningRate   = $this->getEarningRateByQuote($item->getQuote());
        $earningPoints = 0;
        if ($earningRate->isValid()) {
            $this->_eventManager->dispatch('mpreward_before_earning_points', [
                'rate'        => $earningRate,
                'customer_id' => $item->getQuote()->getCustomerId(),
                'type'        => 'earn_rate'
            ]);
            $itemPrice = $this->getItemTotalForDiscount($item, false);

            $earningPoints = $itemPrice / $earningRate->getMoney() * $earningRate->getData('points');
            $earningPoints = $this->getPointHelper()->round($earningPoints);
            $item->setMpRewardEarn($item->getMpRewardEarn() + $earningPoints);
        }
        $oldMpReward = $item->getMpRewardEarn();
        $this->_eventManager->dispatch('mpreward_calculate_item_earning_points', [
            'total'   => $this->getData('total'),
            'item'    => $item,
            'subject' => $this
        ]);

        /** Calculate catalog earning after */
        if ($item->getMpRewardEarn() > $oldMpReward) {
            $earningPoints += (int) $item->getMpRewardEarn() - $oldMpReward;
        }

        return $earningPoints;
    }

    /**
     * @param Item $item
     */
    public function setLastItemMatchRule($item)
    {
        $this->lastItemMathRule = $item;
    }

    /**
     * @return mixed
     */
    public function getLastItemMatchRule()
    {
        return $this->lastItemMathRule;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function calculateShippingEarningPoints(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $this->_eventManager->dispatch('mpreward_calculate_shipping_earning_points_before', [
            'quote'              => $quote,
            'shippingAssignment' => $shippingAssignment,
            'total'              => $total
        ]);

        $storeId = $quote->getStoreId();
        if (!$this->isEarnPointFromShipping($storeId)) {
            return $quote->getMpRewardShippingEarn();
        }

        $earningRate = $this->getEarningRateByQuote($quote);
        if ($earningRate->isValid()) {
            /**
             *  $total->getBaseMpShippingDiscountAmount() is amount discount of mageplaza extensions
             *    $shippingAmount = $total->getBaseShippingAmount() - $total->getBaseShippingDiscountAmount() -
             * $total->getBaseMpShippingDiscountAmount();
             */
            $shippingAmount = $total->getBaseShippingAmount() - $total->getBaseShippingDiscountAmount();
            if ($this->isEarnPointFromTax($storeId)) {
                $shippingAmount += $total->getBaseShippingTaxAmount();
            }

            $earningPoints = $shippingAmount / $earningRate->getMoney() * $earningRate->getData('points');
            $quote->setMpRewardShippingEarn($quote->getMpRewardShippingEarn() + $earningPoints);
        }

        $this->_eventManager->dispatch('mpreward_calculate_shipping_earning_points_after', [
            'quote'              => $quote,
            'shippingAssignment' => $shippingAssignment,
            'total'              => $total
        ]);

        return $quote->getMpRewardShippingEarn();
    }

    /**************************************** Spending Slider **********************************************************
     *
     * @param Quote $quote
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSpendingConfiguration($quote)
    {
        $spendingConfig = [
            'pointSpent'  => $quote->getMpRewardSpent(),
            'ruleApplied' => $quote->getMpRewardApplied(),
            'rules'       => []
        ];

        $rate = $this->getSpendingRateByQuote($quote);
        if ($maxSpending = $this->getMaxSpendingPointsByRate($quote, $rate)) {
            $spendingConfig['rules'][] = [
                'id'              => 'rate',
                'label'           => __(
                    'Each of %1 gets %2 discount',
                    $this->getPointHelper()->format($rate->getPoints(), false, $quote->getStoreId()),
                    $this->convertPrice($rate->getMoney(), true, false, $quote->getStoreId())
                ),
                'min'             => $rate->getMinPoint(),
                'max'             => $maxSpending,
                'step'            => 1,
                'isDisplaySlider' => true
            ];
            if ($rate->getMinPoint() > $quote->getMpRewardSpent()) {
                $spendingConfig['pointSpent'] = $rate->getMinPoint();
            }
        }

        return $spendingConfig;
    }

    /**
     * Is allow spending point on order
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function isAllowSpending($quote)
    {
        $customerId = $quote->getCustomerId();
        if (!$customerId) {
            return false;
        }

        $account  = $this->getAccountHelper()->getByCustomerId($customerId);
        $balance  = $account->getBalance();
        $minSpend = $this->getConfigSpending('minimum_balance', $quote->getStoreId());

        return $balance > 0 && $balance >= $minSpend;
    }

    /**
     * @param Quote $quote
     * @param bool $isCanCalculateRewardDiscount
     * @param bool $isCalculateInvitedDiscount
     *
     * @return null
     */
    public function getSpendingTotal($quote, $isCanCalculateRewardDiscount = true, $isCalculateInvitedDiscount = false)
    {
        $storeId = $quote->getStoreId();
        $total   = 0;

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var Item $child */
                foreach ($item->getChildren() as $child) {
                    $total += $this->getItemTotalForDiscount(
                        $child,
                        true,
                        $isCanCalculateRewardDiscount,
                        $isCalculateInvitedDiscount
                    );
                }
            } else {
                $total += $this->getItemTotalForDiscount(
                    $item,
                    true,
                    $isCanCalculateRewardDiscount,
                    $isCalculateInvitedDiscount
                );
            }
        }
        if ($this->isSpendingOnShippingFee($storeId)) {
            $total += $this->getShippingTotalForDiscount(
                $quote,
                $isCanCalculateRewardDiscount,
                $isCalculateInvitedDiscount
            );
        }



        return $total;
    }

    /**
     * @param Item $item
     * @param bool $isSpending
     * @param bool $isCalculateMpDiscount
     * @param bool $isCalculateInvitedDiscount
     *
     * @return mixed
     */
    public function getItemTotalForDiscount(
        $item,
        $isSpending = true,
        $isCalculateMpDiscount = true,
        $isCalculateInvitedDiscount = false
    ) {
        if ($item->getBaseRowTotal() == 0) {
            return 0;
        }
        /** base_mp_discount_amount is the discount amount of Mageplaza extensions */
        $total = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();

        if ($isCalculateMpDiscount) {
            $total -= $item->getMpRewardBaseDiscount();
        }

        if ($isCalculateInvitedDiscount) {
            $total = $total - $item->getMpRewardInvitedBaseDiscount();
        }
        $isCalculateTax = $isSpending ?
            $this->isSpendingFromTax($item->getStoreId()) :
            $this->isEarnPointFromTax($item->getStoreId());
        if ($isCalculateTax) {
            $total += $item->getBaseTaxAmount();
        }

        /** Compatible Mageplaza Store Credit */

        if ($this->moduleIsEnable('Mageplaza_StoreCredit')) {
            $total -= $this->getStoreCreditDiscountForEachItem($item);
        }

        return $total;
    }

    /**
     * @param Item $item
     *
     * @return int
     */
    public function getStoreCreditDiscountForEachItem($item)
    {
        $amount                    = 0;
        $mpStoreCreditBaseDiscount = floatval($item->getQuote()->getMpStoreCreditBaseDiscount());
        $itemQty                   = $item->getQuote()->getItemsQty();

        if ($mpStoreCreditBaseDiscount > 0 && $itemQty > 0) {
            $amount = $mpStoreCreditBaseDiscount / $itemQty * $item->getQty();
        }

        return $amount;
    }

    /**
     * @param Quote $quote
     * @param bool $isCanCalculateRewardDiscount
     * @param bool $isCalculateInvitedDiscount
     * @param bool $isSpending
     *
     * @return float|int
     */
    public function getShippingTotalForDiscount(
        $quote,
        $isCanCalculateRewardDiscount = true,
        $isCalculateInvitedDiscount = false,
        $isSpending = true
    ) {
        $total = 0;
        if (!$quote->getIsVirtual()) {
            $quoteAddress   = $quote->getShippingAddress();
            $total          = $quoteAddress->getBaseShippingAmount() - $quoteAddress->getBaseShippingDiscountAmount();
            $isCalculateTax = $isSpending ?
                $this->isSpendingFromTax($quote->getStoreId()) :
                $this->isEarnPointFromTax($quote->getStoreId());

            if ($isCalculateTax) {
                $total += $quoteAddress->getBaseShippingTaxAmount();
            }

            if ($isCanCalculateRewardDiscount) {
                $total -= $quote->getMpRewardShippingBaseDiscount();
            }

            if ($isCalculateInvitedDiscount) {
                $total -= $quote->getMpRewardShippingInvitedBaseDiscount();
            }
        }

        return $total;
    }

    /**
     * get amount and base amount
     *
     * @param $item
     * @param $shippingTotal
     * @param $spendingTotal
     * @param $totalDiscountByRate
     * @return array
     */
    public function getRewardDiscount($item, $baseTotal) {
        $baseDiscount = $this->roundPrice($baseTotal, 'base');
        $discount     = $this->convertPrice(
            $baseDiscount,
            false,
            false,
            $item->getStoreId()
        );
        $discount    = $this->roundPrice($discount);

        return [$baseDiscount, $discount];
    }

    /**
     * @param Quote $quote
     * @param null $rate
     *
     * @return int|mixed
     * @throws NoSuchEntityException
     */
    public function getMaxSpendingPointsByRate($quote, $rate = null)
    {
        $spendingRate = $rate ?: $this->getSpendingRateByQuote($quote);
        if (!$spendingRate->isValid()) {
            return 0;
        }

        $spentTotal = $this->getSpendingTotal($quote, false);
        if ($quote->getMpRewardInvitedBaseDiscount()) {
            $spentTotal -= $quote->getMpRewardInvitedBaseDiscount();
        }


        $quoteTotal = $quote->getBaseSubtotal();
        if ($this->isSpendingOnShippingFee($quote->getStoreId())) {
            $quoteTotal += $this->getShippingTotalForDiscount(
                $quote,
                false,
                true
            );
        }
        if ($this->isSpendingFromTax($quote->getStoreId())) {
            foreach ($quote->getAllItems() as $item) {
                $quoteTotal += $item->getBaseTaxAmount();
            }
        }
        $maxQuoteSpentPointByQuote = floor(
            $quoteTotal * $spendingRate->getPoints() / $spendingRate->getMoney()
        );

        return $this->getMaxSpendingPoints(
            floor($spentTotal * $spendingRate->getPoints() / $spendingRate->getMoney()),
            $maxQuoteSpentPointByQuote
        );
    }

    /**
     * @param float $maxPointsByRule
     * @param float $maxQuoteSpentPointByQuote
     *
     * @return mixed
     */
    public function getMaxSpendingPoints($maxPointsByRule, $maxQuoteSpentPointByQuote = null)
    {
        $quote               = $this->getQuote();
        $account             = $this->getAccountHelper()->getByCustomerId($quote->getCustomerId());
        $maxSpending         = $account->getBalance();
        $maxSpendingPerOrder = (int) $this->getMaximumSpendingPointsPerOrder($quote->getStoreId());

        if ($maxSpendingPerOrder) {
            $typeMaxSpending = (int) $this->getTypeMaximumSpendingPoints($quote->getStoreId());
            if ($typeMaxSpending === 1 && $maxQuoteSpentPointByQuote) {
                $maxSpendingPerOrder = floor($maxQuoteSpentPointByQuote * $maxSpendingPerOrder / 100);
            }
            $maxSpending = min($maxSpending, $maxSpendingPerOrder);
        }

        return min($maxPointsByRule, $maxSpending);
    }

    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     *
     * @return float
     */
    public function roundPrice($price, $type = 'regular')
    {
        if ($price) {
            if (!isset($this->_calculators[$type])) {
                $this->_calculators[$type] = $this->_calculatorFactory->create();
            }
            $price = $this->_calculators[$type]->deltaRound($price);
        }

        return $price;
    }

    /********************************* Retrieve Reward Points Rate ****************************************************
     *
     * @param int $direction
     * @param int|null $websiteId
     * @param int|null $customerGroupId
     *
     * @return Rate
     * @throws NoSuchEntityException
     */
    public function getRate($direction, $websiteId = null, $customerGroupId = null)
    {
        $websiteId       = $websiteId ?: $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = isset($customerGroupId) ? $customerGroupId : $this->getQuote()->getCustomerGroupId();

        $cacheKey = 'rate_' . $direction . '_' . $websiteId . '_' . $customerGroupId;
        if (!$this->getData($cacheKey)) {
            $collection = $this->objectManager->create(Collection::class);
            if (!$this->storeManager->isSingleStoreMode()) {
                $collection->addFieldToFilter('website_ids', ['finset' => $websiteId]);
            }
            $collection->addFieldToFilter('direction', $direction)
                ->addFieldToFilter('customer_group_ids', ['finset' => $customerGroupId])
                ->addFieldToFilter('points', ['gt' => 0])
                ->addFieldToFilter('money', ['gt' => 0])
                ->setOrder('priority', 'ASC');

            $this->setData($cacheKey, $collection->getFirstItem());
        }

        return $this->getData($cacheKey);
    }

    /**
     * @param int|null $websiteId
     * @param int|null $customerGroupId
     *
     * @return Rate
     * @throws NoSuchEntityException
     */
    public function getEarningRate($websiteId = null, $customerGroupId = null)
    {
        return $this->getRate(Direction::MONEY_TO_POINT, $websiteId, $customerGroupId);
    }

    /**
     * @param null $websiteId
     * @param null $customerGroupId
     *
     * @return Rate
     * @throws NoSuchEntityException
     */
    public function getSpendingRate($websiteId = null, $customerGroupId = null)
    {
        return $this->getRate(Direction::POINT_TO_MONEY, $websiteId, $customerGroupId);
    }

    /**
     * @param Quote $quote
     *
     * @return Rate
     * @throws NoSuchEntityException
     */
    public function getEarningRateByQuote($quote)
    {
        $storeId   = $quote->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        return $this->getEarningRate($websiteId, $quote->getCustomerGroupId());
    }

    /**
     * @param Quote $quote
     *
     * @return Rate
     * @throws NoSuchEntityException
     */
    public function getSpendingRateByQuote($quote)
    {
        $storeId   = $quote->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        $rate = $this->getSpendingRate($websiteId, $quote->getCustomerGroupId());

        $this->_eventManager->dispatch('mpreward_before_spending_points', [
            'rate'        => $rate,
            'customer_id' => $quote->getCustomerId(),
            'type'        => 'spend_rate'
        ]);

        return $rate;
    }

    /**
     * @param Order $order
     * @param string $action
     * @param string $field
     *
     * @return int
     */
    public function calculatePointOrderCompleteByAction($order, $action, $field = 'mp_reward_earn')
    {
        $pointAmount = 0;
        if (!$this->isEarnPointAfterInvoiceCreated() &&
            $order->getState() === Order::STATE_COMPLETE &&
            $order->getData($field) > 0) {
            $transaction = $this->transactionFactory->create()->getCollection()
                ->addFieldToFilter('action_code', $action)
                ->addFieldToFilter('order_id', $order->getId())
                ->getFirstItem();
            if ($transaction->getId()) {
                return $pointAmount;
            }

            if ($order->hasCreditmemos()) {
                foreach ($order->getItems() as $item) {
                    $qty = $item->getQtyOrdered() - $item->getQtyRefunded();
                    if ($item->getData($field) > 0 && $qty > 0) {
                        $pointAmount += $item->getData($field) * $qty / $item->getQtyOrdered();
                    }
                }
            } else {
                $pointAmount = $order->getData($field);
            }
        }

        return $pointAmount;
    }

    /**
     * @param Item $items
     * @param Quote $quote
     * @param array $quoteFields
     * @param array $itemFields
     */
    public function resetRewardData($items, $quote, $quoteFields = [], $itemFields = [])
    {
        $this->resetFields($quote, $quoteFields);

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var Item $child */
                foreach ($item->getChildren() as $child) {
                    $this->resetFields($child, $itemFields);
                }
            } else {
                $this->resetFields($item, $itemFields);
            }
        }
    }

    /**
     * @param Quote|Item $object
     * @param array $fields
     */
    public function resetFields($object, $fields)
    {
        foreach ($fields as $field) {
            $object->setData($field, 0);
        }
    }

    /**
     * @param Quote $quote
     *
     * @throws LocalizedException
     */
    public function addLocalizedException($quote)
    {
        $quote->setMpRewardSpent(0)->setMpRewardApplied(null)->save();
        if ($this->isArea(Area::AREA_WEBAPI_REST) || $this->_getRequest()->isXmlHttpRequest()) {
            throw new LocalizedException(
                __('Invalid rule or rule not active.')
            );
        }
    }
}
