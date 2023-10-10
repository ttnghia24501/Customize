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

use DateTimeZone;
use Exception;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\FeedFactory;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Generate
 * @package Mageplaza\ProductFeed\Cron
 */
class Generate
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var File
     */
    protected $file;

    /**
     * Generate constructor.
     *
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     * @param File $file
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        Data $helper,
        DateTime $dateTime,
        CollectionFactory $collectionFactory,
        File $file,
        FeedFactory $feedFactory
    ) {
        $this->logger            = $logger;
        $this->helper            = $helper;
        $this->timezone          = $timezone;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime          = $dateTime;
        $this->feedFactory       = $feedFactory;
        $this->file              = $file;
    }

    /**
     * Send Mail
     *
     * @return void
     */
    public function execute()
    {
        if ($this->helper->isEnabled()) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('execution_mode', 'cron');
            $collection->walk([$this, 'generate']);
        }
    }

    /**
     * @param Feed $feed
     */
    public function generate($feed)
    {
        $cronRunTime = $feed->getCronRunTime();
        $lastCron    = $feed->getLastCron();
        $frequency   = $feed->getFrequency();
        $dayOfWeek   = $feed->getCronRunDayOfWeek();
        $dayOfMonth  = $feed->getCronRunDayOfMonth();
        if ($this->isGenerate($cronRunTime, $lastCron, $frequency, $dayOfWeek, $dayOfMonth)) {
            try {
                $productCount = $this->helper->prepareRunCron($feed);
                $this->prepareProductData($feed, $productCount);
                $this->helper->generateAndDeliveryFeed($feed, false, true, false);
                $this->feedFactory->create()->load($feed->getId())->setLastCron($this->dateTime->date())->save();
                $this->file->deleteDirectory(Data::FEED_FILE_PATH . 'collection/' . $feed->getId() . '/');
                $this->file->deleteDirectory(Data::FEED_FILE_PATH . 'cron/');
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Check time to generate feed
     *
     * @param $cronRunTime
     * @param $lastCron
     * @param $frequency
     * @param $dayOfWeek
     * @param $dayOfMonth
     *
     * @return bool
     */
    public function isGenerate($cronRunTime, $lastCron, $frequency, $dayOfWeek, $dayOfMonth)
    {
        $lastCronTime = $this->dateTime->date('Y-m-d', $lastCron);

        $time             = explode(',', $cronRunTime);
        $cronRunTimeStamp = $this->timezone->date()
            ->setTimezone(new DateTimeZone('UTC'))->setTime($time[0], $time[1])->getTimestamp();
        $date             = $this->dateTime->date('Y-m-d', strtotime('now'));

        switch ($frequency) {
            case Frequency::CRON_DAILY:
                return ($lastCronTime < $date || !$lastCron) && (time() >= $cronRunTimeStamp);
            case Frequency::CRON_WEEKLY:
                return date('w') === $dayOfWeek && time() >= $cronRunTimeStamp
                    && ($lastCronTime < $date || !$lastCron);
            case Frequency::CRON_MONTHLY:
                return date('j') === $dayOfMonth && time() >= $cronRunTimeStamp
                    && ($lastCronTime < $date || !$lastCron);
        }

        return ($lastCronTime < $date || !$lastCron) && (time() >= $cronRunTimeStamp);
    }

    /**
     * Prepare product data
     *
     * @param Feed $feed
     * @param int $productCount
     *
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareProductData($feed, $productCount)
    {
        $productChunk = $this->file->fileGetContents(Data::FEED_FILE_PATH . 'cron/productIds/' . $feed->getId());
        $productAttr  = $this->file->fileGetContents(Data::FEED_FILE_PATH . 'cron/prdAttr/' . $feed->getId());

        $productChunk = $this->helper->unserialize($productChunk);
        $productAttr  = $this->helper->unserialize($productAttr);
        $current      = 0;
        while ($current < $productCount) {
            $ids        = (array) array_shift($productChunk);
            $collection = $this->helper->getProductsData($feed, $productAttr, $ids);
            $name       = $ids ? current($ids) . end($ids) : '0';
            $this->helper->createFeedCollectionFile($feed->getId(), Data::jsonEncode($collection), $name);
            $current += count($ids);
        }
    }
}
