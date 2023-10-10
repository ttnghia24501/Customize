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

namespace Mageplaza\ProductFeed\Controller\Adminhtml\Report;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Mageplaza\ProductFeed\Block\Adminhtml\RenderReport;
use Mageplaza\ProductFeed\Model\FeedFactory;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\CollectionFactory as FeedCollection;
use Mageplaza\ProductFeed\Model\ResourceModel\Reports\Collection;
use Mageplaza\ProductFeed\Model\ResourceModel\Reports\CollectionFactory as ReportCollection;

/**
 * Class GetData
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\Report
 */
class GetData extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var ReportCollection
     */
    protected $reportCollection;

    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var OrderCollection
     */
    protected $orderCollection;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param FeedFactory $feedFactory
     * @param ReportCollection $reportCollection
     * @param FeedCollection $feedCollection
     * @param OrderCollection $orderCollection
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        FeedFactory $feedFactory,
        ReportCollection $reportCollection,
        FeedCollection $feedCollection,
        OrderCollection $orderCollection,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->feedFactory        = $feedFactory;
        $this->reportCollection   = $reportCollection;
        $this->feedCollection     = $feedCollection;
        $this->orderCollection    = $orderCollection;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $result     = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $date       = $this->getRequest()->getParam('date');
        $reportData = $this->getReportData($date);
        $chartData  = $this->getChartData($reportData, $date);

        $data = ['report' => $reportData];

        $block = $resultPage->getLayout()
            ->createBlock(RenderReport::class)
            ->setTemplate('Mageplaza_ProductFeed::report/render.phtml')
            ->setData('data', $data)
            ->toHtml();

        $result->setData(['table' => $block, 'chart' => $chartData]);

        return $result;
    }

    /**
     * @param array $dateRange
     *
     * @return array
     */
    public function getReportData($dateRange)
    {
        $reportData = [];
        $feedIds    = $this->feedCollection->create()->getAllIds();

        foreach ($feedIds as $feedId) {
            $collection = $this->getReportCollection($dateRange)->addFieldToFilter('feed_id', $feedId)
                ->addExpressionFieldToSelect('order_item_qty', 'SUM({{ordered_quantity}})', 'ordered_quantity')
                ->addExpressionFieldToSelect('feed_revenue', 'SUM({{revenue}})', 'revenue')
                ->addExpressionFieldToSelect('feed_refund', 'SUM({{refunded}})', 'refunded')
                ->addExpressionFieldToSelect('feed_discount', 'SUM({{discount}})', 'discount')
                ->addExpressionFieldToSelect('feed_tax', 'SUM({{tax}})', 'tax');

            $feedReportData                     = $collection->getFirstItem();
            $reportData[$feedId]                = $feedReportData;
            $reportData[$feedId]['order_count'] = $this->countOrder($dateRange, $feedId);
        }

        return $reportData;
    }

    /**
     * @param array $dateRange
     *
     * @return Collection
     */
    protected function getReportCollection($dateRange)
    {
        return $this->reportCollection->create()
            ->addFieldToFilter('created_at', ['lteq' => $dateRange[1]])
            ->addFieldToFilter('created_at', ['gteq' => $dateRange[0]]);
    }

    /**
     * @param array $dateRange
     * @param int $feedId
     *
     * @return int
     */
    public function countOrder($dateRange, $feedId)
    {
        $orderIds        = $this->getReportCollection($dateRange)->addFieldToFilter('feed_id', $feedId)
            ->getColumnValues('order_id');
        $orderCollection = $this->orderCollection->create()->addFieldToFilter('entity_id', ['in' => $orderIds])
            ->addFieldToFilter('status', ['neq' => 'closed']);

        return $orderCollection->getSize();
    }

    /**
     * @param array $reportData
     * @param array $date
     *
     * @return array
     */
    public function getChartData($reportData, $date)
    {
        $chartData    = [];
        $totalRevenue = (float) $this->getTotalRevenue($date);
        $reportData   = array_slice($reportData, 0, 20, true);

        if ($totalRevenue) {
            foreach ($reportData as $key => $value) {
                $feedLabel   = $this->feedFactory->create()->load($key)->getName();
                $feedRevenue = (float) $value['feed_revenue'] - $value['feed_refund'];
                if ($feedRevenue > 0) {
                    $chartData['chartLabel'][] = $feedLabel;
                    $revenuePercent            = number_format($feedRevenue / $totalRevenue * 100, 2);
                    $chartData['revenue'][]    = $revenuePercent;
                }
            }
        }
        if (array_key_exists('revenue', $chartData) && count($chartData['revenue'])) {
            array_multisort($chartData['revenue'], SORT_DESC, $chartData['chartLabel']);
        }

        return $chartData;
    }

    /**
     * @param array $dateRange
     *
     * @return mixed|null
     */
    public function getTotalRevenue($dateRange)
    {
        $reportCollection = $this->getReportCollection($dateRange);
        $collection       = $reportCollection->addExpressionFieldToSelect('totals', 'SUM({{revenue}})', 'revenue')
            ->addExpressionFieldToSelect('refunded', 'SUM({{refunded}})', 'refunded');

        $record = $collection->getFirstItem();

        return $record['totals'] - $record['refunded'];
    }
}
