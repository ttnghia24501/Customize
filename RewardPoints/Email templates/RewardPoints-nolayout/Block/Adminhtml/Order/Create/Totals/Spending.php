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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Order\Create\Totals;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals;
use Magento\Sales\Helper\Data;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Config;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Spending
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Order\Create\Totals
 */
class Spending extends DefaultTotals
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Mageplaza_RewardPoints::order/create/totals/spending.phtml';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Earning constructor.
     *
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $salesData
     * @param Config $salesConfig
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        Data $salesData,
        Config $salesConfig,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesData, $salesConfig, $data);
    }

    /**
     * @param int $amount
     *
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function formatPoints($amount)
    {
        return $this->helperData->getPointHelper()->format((int) $amount, false);
    }
}
