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
 * @package     Mageplaza_Security
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Console\Command;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollection;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\CollectionFactory as FeedCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReportData
 * @package Mageplaza\ProductFeed\Console\Command
 */
class ReportData extends Command
{
    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var InvoiceCollection
     */
    protected $invoiceCollection;

    /**
     * @var CreditmemoCollection
     */
    protected $creditmemoCollection;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var array
     */
    protected $reportData;

    /**
     * Generate constructor.
     *
     * @param Data $helper
     * @param FeedCollection $feedCollection
     * @param InvoiceCollection $invoiceCollection
     * @param CreditmemoCollection $creditmemoCollection
     * @param ResourceConnection $resourceConnection
     * @param State $state
     * @param null $name
     */
    public function __construct(
        Data $helper,
        FeedCollection $feedCollection,
        InvoiceCollection $invoiceCollection,
        CreditmemoCollection $creditmemoCollection,
        ResourceConnection $resourceConnection,
        State $state,
        $name = null
    ) {
        $this->feedCollection       = $feedCollection;
        $this->invoiceCollection    = $invoiceCollection;
        $this->creditmemoCollection = $creditmemoCollection;
        $this->helper               = $helper;
        $this->state                = $state;
        $this->resourceConnection   = $resourceConnection;
        $this->reportData           = [];

        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $output->writeln('<info>Running...</info>');
        $feedCollection = $this->feedCollection->create()->addFieldToFilter('status', 1);
        foreach ($feedCollection as $feed) {
            $storeIds             = explode(',', $feed->getStoreId());
            $invoiceCollection    = $this->invoiceCollection->create()
                ->addFieldToFilter('created_at', ['gteq' => $feed->getCreatedAt()]);
            $creditmemoCollection = $this->creditmemoCollection->create()
                ->addFieldToFilter('created_at', ['gteq' => $feed->getCreatedAt()]);
            $this->helper->addStoreFilter($invoiceCollection, $storeIds);
            $this->helper->addStoreFilter($creditmemoCollection, $storeIds);
            $this->getReportData($invoiceCollection, $feed, 'invoice');
            $this->getReportData($creditmemoCollection, $feed, 'creditmemo');
        }

        if (count($this->reportData)) {
            $reportTable = $this->resourceConnection->getTableName('mageplaza_productfeed_reports');
            try {
                $this->resourceConnection->getConnection()->delete($reportTable);
                $this->resourceConnection->getConnection()->insertMultiple($reportTable, $this->reportData);
            } catch (Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
            }
        }
        $output->writeln('<info>The feed report generated Successfully!</info>');

        return true;
    }

    /**
     * @param $collection
     * @param Feed $feed
     * @param string $type
     *
     * @return void
     */
    protected function getReportData($collection, $feed, $type)
    {
        foreach ($collection as $data) {
            $items = $data->getAllItems();
            foreach ($items as $item) {
                if (!$item->getOrderItem()->getMpProductfeedKey()) {
                    continue;
                }
                $orderItemFeedKeys = explode(',', $item->getOrderItem()->getMpProductfeedKey());
                $feedIds           = [];
                foreach ($orderItemFeedKeys as $feedKey) {
                    $feedIds[] = $this->helper->feedKeyDecode($feedKey);
                }
                if (in_array($feed->getId(), $feedIds)) {
                    $addData = [
                        'feed_id'    => $feed->getId(),
                        'order_id'   => $data->getOrderId(),
                        'created_at' => $data->getCreatedAt()
                    ];
                    if ($type == 'invoice') {
                        $addData['ordered_quantity'] = $item->getQty();
                        $addData['revenue']          = $item->getBaseRowTotal();
                        $addData['refunded']         = 0;
                        $addData['discount']         = $item->getBaseDiscountAmount() ?: 0;
                        $addData['tax']              = $item->getBaseTaxAmount();
                    } else {
                        $addData['ordered_quantity'] = -1 * $item->getQty();
                        $addData['revenue']          = 0;
                        $addData['refunded']         = $item->getBaseRowTotal();
                        $addData['discount']         = -1 * $item->getBaseDiscountAmount() ?: 0;
                        $addData['tax']              = -1 * $item->getBaseTaxAmount();
                    }
                    $this->reportData[] = $addData;
                }
            }
        }
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('mp-productfeed:report')
            ->setDescription('Generate Feed Report via command line');

        parent::configure();
    }
}
