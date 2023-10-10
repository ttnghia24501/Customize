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
 * @package     Mageplaza_Customize
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Customize\Cron;

use Exception;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Mageplaza\Customize\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class SubscriptionCron
 *
 * @package Mageplaza\Customize\Cron
 */
class SubscriptionCron
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var SubscriberCollectionFactory
     */
    protected $subscriberCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Data $helperData
     * @param SubscriberCollectionFactory $subscriberCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helperData,
        SubscriberCollectionFactory $subscriberCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->helperData                  = $helperData;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->logger                      = $logger;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $storeId = $this->helperData->getStoreId();
        $sender  = $this->helperData->getSender($storeId);
        $sendTo  = $this->helperData->getSendTo($storeId);

        if ($this->helperData->isEnabled($storeId)) {
            try {
                $subscribers = $this->getSubscribersToProcess();

                foreach ($subscribers as $subscriber) {
                    $customer = $this->helperData->getCustomerById($subscriber->getCustomerId());
                    $sendTo[] = $customer->getEmail();

                    if ($subscriber->isSubscribed() === Subscriber::STATUS_SUBSCRIBED) {
                        $this->helperData->sendMail(
                            $sendTo,
                            $customer,
                            Data::XML_PATH_SUBSCRIPTION_EMAIL_TYPE,
                            $storeId,
                            $sender
                        );
                    } elseif ($subscriber->isSubscribed() === Subscriber::STATUS_UNSUBSCRIBED) {
                        $this->helperData->sendMail(
                            $sendTo,
                            $customer,
                            Data::XML_PATH_UNSUBSCRIPTION_EMAIL_TYPE,
                            $storeId,
                            $sender
                        );
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    private function getSubscribersToProcess()
    {
        $subscribers = [];

        $subscriberCollection = $this->subscriberCollectionFactory->create();

        foreach ($subscriberCollection as $subscriber) {
            $subscribers[] = $subscriber;
        }

        return $subscribers;
    }
}
