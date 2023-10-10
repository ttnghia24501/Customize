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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;
use Magento\Sales\Model\AdminOrder\Create;
use Mageplaza\RewardPoints\Helper\Calculation;
use Mageplaza\RewardPoints\Model\Source\DisplayPointLabel;

/**
 * Class SpendingPoints
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Order\Create
 */
class SpendingPoints extends AbstractCreate
{
    /**
     * @var Calculation
     */
    protected $helperCalculation;

    /**
     * SpendingPoints constructor.
     *
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param Calculation $helperCalculation
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        Calculation $helperCalculation,
        array $data = []
    ) {
        $this->helperCalculation = $helperCalculation;

        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_create_mp_reward_spending_points_form');
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRewardSpendingConfig()
    {
        $quote = $this->getQuote();
        $quote->setItems($quote->getAllItems());
        $storeId        = $quote->getStoreId();
        $pointHelper    = $this->helperCalculation->getPointHelper();
        $isLabelBefore  = (int) $pointHelper->getPointLabelPosition($storeId) === DisplayPointLabel::BEFORE_AMOUNT;
        $spendingConfig = [
            'pointSpent'   => null,
            'ruleApplied'  => null,
            'rules'        => [],
            'useMaxPoints' => false,
        ];

        if ($this->helperCalculation->isAllowSpending($quote)) {
            $spendingConfig                 = $this->helperCalculation->getSpendingConfiguration($quote);
            $spendingConfig['useMaxPoints'] = (bool) $this->helperCalculation->getConfigSpending(
                'use_max_point',
                $storeId
            );
        }

        $rewardSpendingConfig = [
            'pattern'  => [
                'single' => $isLabelBefore ? $pointHelper->getPointLabel($storeId) . ' {point}'
                    : '{point} ' . $pointHelper->getPointLabel($storeId),
                'plural' => $isLabelBefore ? $pointHelper->getPluralPointLabel($storeId) . ' {point}'
                    : '{point} ' . $pointHelper->getPluralPointLabel($storeId)
            ],
            'balance'  => $this->helperCalculation->getAccountHelper()->create($this->getCustomerId())->getBalance(),
            'spending' => $spendingConfig
        ];

        return $rewardSpendingConfig;
    }

    /**
     * @param null $storeId
     * @return array|bool
     */
    public function isEnabled($storeId = null) {
        return $this->helperCalculation->isEnabled($storeId);
    }
}
