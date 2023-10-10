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

namespace Mageplaza\Customize\Observer;

use Exception;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Customize\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class NewsletterObserver
 *
 * @package Mageplaza\Customize\Observer
 */
class NewsletterObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Http
     */
    protected $request;

    /**
     * NewsletterObserver constructor.
     *
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param Http $request
     */
    public function __construct(
        Data $helperData,
        LoggerInterface $logger,
        Http $request
    ) {
        $this->helperData = $helperData;
        $this->logger     = $logger;
        $this->request    = $request;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $subscriber        = $observer->getEvent()->getSubscriber();
        $isSubscribedAfter = $subscriber->loadByCustomerId($subscriber->getId())->isSubscribed();
        $customerEmail     = $subscriber->getSubscriberEmail();
        $storeId           = $this->helperData->getStoreId();
        $sender            = $this->helperData->getSender($storeId);
        $sendTo            = $this->helperData->getSendTo($storeId);
        $sendTo[]          = $customerEmail;

        if ($subscriber->getCustomerId()) {
            $customer = $this->helperData->getCustomerById($subscriber->getCustomerId());
        } else {
            $customer = $this->helperData->getCustomerSession();
        }

        if ($this->helperData->isEnabled($storeId) && !$this->helperData->isSendByCron($storeId)) {
            try {
                if ($isSubscribedAfter) {
                    $this->helperData->sendMail($sendTo, $customer, Data::XML_PATH_SUBSCRIPTION_EMAIL_TYPE,
                        $storeId, $sender);
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/byobserver.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('sub observer');
                    $logger->info(json_encode($sendTo));
                } else {
                    $this->helperData->sendMail($sendTo, $customer, Data::XML_PATH_UNSUBSCRIPTION_EMAIL_TYPE,
                        $storeId, $sender);
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/byobserver.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('unsub observer');
                    $logger->info(json_encode($sendTo));
                }

            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
