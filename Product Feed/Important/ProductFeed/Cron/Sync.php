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

namespace Mageplaza\ProductFeed\Cron;

use Exception;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Config\Source\Status;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\HistoryFactory;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\CollectionFactory as FeedCollection;
use Mageplaza\ProductFeed\Model\ResourceModel\History\CollectionFactory as HistoryCollection;
use Psr\Log\LoggerInterface;

/**
 * Class Sync
 * @package Mageplaza\ProductFeed\Cron
 */
class Sync
{
    /**
     * @var HistoryCollection
     */
    protected $historyCollection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * Sync constructor.
     *
     * @param LoggerInterface $logger
     * @param HistoryFactory $historyFactory
     * @param Data $helper
     * @param HistoryCollection $historyCollection
     * @param File $file
     * @param FeedCollection $feedCollection
     */
    public function __construct(
        LoggerInterface $logger,
        HistoryFactory $historyFactory,
        Data $helper,
        HistoryCollection $historyCollection,
        File $file,
        FeedCollection $feedCollection
    ) {
        $this->logger            = $logger;
        $this->historyFactory    = $historyFactory;
        $this->historyCollection = $historyCollection;
        $this->helper            = $helper;
        $this->file              = $file;
        $this->feedCollection    = $feedCollection;
    }

    /**
     * Sync
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $feedCollection = $this->feedCollection->create()
            ->addFieldToFilter('file_type', 'xml')
            ->addFieldToFilter('status', 1);
        foreach ($feedCollection as $feed) {
            $this->sync($feed);
        }
    }

    /**
     * @param Feed $feed
     *
     * @throws Exception
     */
    public function sync($feed)
    {
        try {
            if ($this->checkTime() && $this->helper->useGoogleShoppingApi($feed)) {
                $productCount = $this->helper->prepareRunCron($feed);
                $this->syncProducts($feed, $productCount);

                $history = $this->historyFactory->create();
                $history->setData([
                    'feed_id'         => $feed->getId(),
                    'feed_name'       => $feed->getName(),
                    'status'          => Status::SUCCESS,
                    'type'            => 'sync_cron',
                    'product_count'   => $productCount,
                    'success_message' => __('%1 feed sync successful', $feed->getName())
                ])->save();
                $this->file->deleteDirectory(Data::FEED_FILE_PATH . 'cron/');
            }
        } catch (Exception $e) {
            $history = $this->historyFactory->create();
            $history->setData([
                'feed_id'       => $feed->getId(),
                'feed_name'     => $feed->getName(),
                'status'        => Status::ERROR,
                'type'          => 'sync_cron',
                'error_message' => __('%1 feed sync fail', $feed->getName())
            ])->save();
            $this->logger->critical($e);
        }
    }

    /**
     * Check time run cron
     *
     * @return bool
     */
    public function checkTime()
    {
        $lastSync = $this->historyCollection->create()
            ->addFieldToFilter('type', 'sync_cron')
            ->setOrder('id', 'desc')
            ->getFirstItem();
        if ($lastSync) {
            if (!$lastSync->getCreatedAt()) {
                return true;
            }
            $now          = strtotime("now");
            $lastSyncTime = strtotime($lastSync->getCreatedAt());
            $syncConfig   = $this->helper->getConfigGeneral('google_shopping/sync_every');
            $syncConfig   = $syncConfig * 24 * 60 * 60;
            if ($now - $lastSyncTime >= $syncConfig) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Feed $feed
     * @param int $productCount
     *
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function syncProducts($feed, $productCount)
    {
        $productChunk = $this->file->fileGetContents(Data::FEED_FILE_PATH . 'cron/productIds/' . $feed->getId());
        $productAttr  = $this->file->fileGetContents(Data::FEED_FILE_PATH . 'cron/prdAttr/' . $feed->getId());

        $productChunk = $this->helper->unserialize($productChunk);
        $productAttr  = $this->helper->unserialize($productAttr);
        $productCount = count($feed->getMatchingProductIds());
        $current      = 0;
        while ($current < $productCount) {
            $ids        = array_shift($productChunk);
            $collection = $this->helper->getProductsData($feed, $productAttr, $ids, true);

            $collection->walk([$this, 'syncProduct'], [$feed]);

            $current += count($ids);
        }
    }

    /**
     * @param $args
     *
     * @throws NoSuchEntityException
     */
    public function syncProduct($product, $args)
    {
        $this->helper->syncProductToGoogleShopping($args, $product);
    }
}