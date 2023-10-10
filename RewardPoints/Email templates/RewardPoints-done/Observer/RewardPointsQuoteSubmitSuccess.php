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

namespace Mageplaza\RewardPoints\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class RewardPointsQuoteSubmitSuccess
 * @package Mageplaza\RewardPoints\Observer
 */
class RewardPointsQuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * RewardPointsConvertData constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($quote->getMpRewardSpent() && $quote->getCustomerId()) {
            /** Add spending point transaction */
            $this->helperData->addTransaction(
                HelperData::ACTION_SPENDING_ORDER,
                $quote->getCustomer(),
                -$quote->getMpRewardSpent(),
                $order
            );
        }

        return $this;
    }
}
