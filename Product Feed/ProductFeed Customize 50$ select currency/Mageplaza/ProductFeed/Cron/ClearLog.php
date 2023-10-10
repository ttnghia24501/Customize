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
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\ResourceModel\History\CollectionFactory as HistoryCollection;
use Mageplaza\Smtp\Model\ResourceModel\Log\Collection;
use Psr\Log\LoggerInterface;

/**
 * Class ClearLog
 * @package Mageplaza\ProductFeed\Cron
 */
class ClearLog
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var HistoryCollection
     */
    protected $historyCollection;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * ClearLog constructor.
     *
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param Data $helper
     * @param HistoryCollection $historyCollection
     */
    public function __construct(
        LoggerInterface $logger,
        DateTime $date,
        Data $helper,
        HistoryCollection $historyCollection
    ) {
        $this->logger            = $logger;
        $this->date              = $date;
        $this->historyCollection = $historyCollection;
        $this->helper            = $helper;
    }

    /**
     * Clean Email Log after X day(s)
     *
     * @return $this
     */
    public function execute()
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }

        $day = (int) $this->helper->getConfigGeneral('clear_log');
        if (isset($day) && $day > 0) {
            $timeEnd = strtotime($this->date->date()) - $day * 24 * 60 * 60;

            /** @var Collection $logs */
            $logs = $this->historyCollection->create()
                ->addFieldToFilter('created_at', ['lteq' => date('Y-m-d H:i:s', $timeEnd)]);

            foreach ($logs as $log) {
                try {
                    $log->delete();
                } catch (Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }

        return $this;
    }
}
