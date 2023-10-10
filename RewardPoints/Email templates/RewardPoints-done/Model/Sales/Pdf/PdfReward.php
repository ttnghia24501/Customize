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

namespace Mageplaza\RewardPoints\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class PdfReward
 * @package Mageplaza\RewardPoints\Model\Sales\Pdf
 */
abstract class PdfReward extends DefaultTotal
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var $isUsePointLabel
     */
    protected $isUsePointLabel = true;

    /**
     * RewardDiscount constructor.
     *
     * @param Data $taxHelper
     * @param Calculation $taxCalculation
     * @param CollectionFactory $ordersFactory
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        if (intval($this->getAmount()) !== 0) {
            $totals = [
                [
                    'amount' => $this->getAmountFormat(),
                    'label' => __($this->getLabel()),
                    'font_size' => $fontSize,
                    'sort_order' => $this->getSortOrder()
                ],
            ];

            return $totals;
        }
        return [];
    }

    /**
     * @return string
     */
    public function getSortOrder()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return '';
    }

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAmountFormat()
    {
        $storeId = $this->getOrder()->getStoreId();

        return $this->isUsePointLabel
            ? $this->helperData->getPointHelper()->format(round($this->getAmount()), false, $storeId)
            : $this->getOrder()->formatPriceTxt($this->getAmount());
    }
}
