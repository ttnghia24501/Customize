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
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Block\Adminhtml;

use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\Collection;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\CollectionFactory as FeedCollection;

/**
 * Class RenderReport
 * @package Mageplaza\ProductFeed\Block\Adminhtml
 */
class RenderReport extends Template
{
    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param Context $context
     * @param FeedCollection $feedCollection
     * @param PriceHelper $priceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        FeedCollection $feedCollection,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->feedCollection   = $feedCollection;
        $this->priceHelper      = $priceHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getListFeed()
    {
        return $this->feedCollection->create();
    }

    /**
     * @param int $feedId
     *
     * @return array
     */
    public function getReportData($feedId)
    {
        $reportData = $this->_data['data']['report'][$feedId];

        return $reportData;
    }

    /**
     * @param float $amount
     *
     * @return float|string
     */
    public function formatPrice($amount)
    {
        $amount = $amount > 0 ? $amount : 0;

        return $this->priceHelper->currency($amount, true, false);
    }

    /**
     * @param int $qty
     *
     * @return string
     */
    public function formatQty($qty)
    {
        if ($qty - (int) $qty > 0) {
            return number_format($qty, 2);
        }

        return number_format($qty, 0);
    }
}
