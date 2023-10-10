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
 * @package     Mageplaza_Customize
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Customize\Observer\Sms;

use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Mageplaza\SmsNotification\Helper\Sms as SMSHelperData;
use Mageplaza\SmsNotification\Model\SmsFactory;

/**
 * Class CustomerRegisterSuccess
 * @package Mageplaza\Customize\Observer\Sms
 */
class CustomerRegisterSuccess implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var SMSHelperData
     */
    protected $helperSms;

    /**
     * @var SmsFactory
     */
    private $smsFactory;

    /**
     * NewsletterSubscriber constructor.
     *
     * @param LoggerInterface $logger
     * @param SMSHelperData $helperSms
     * @param SmsFactory $smsFactory
     */
    public function __construct(
        LoggerInterface $logger,
        SMSHelperData $helperSms,
        SmsFactory $smsFactory
    ) {
        $this->_logger    = $logger;
        $this->helperSms  = $helperSms;
        $this->smsFactory = $smsFactory;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        try {
            $storeId = $customer->getStoreId();
            if ($this->helperSms->isEnabled($storeId)) {
                $recipient      = $customer->getCustomAttribute('mp_sms_telephone')->getValue();
                $isSmsSubscribe = $customer->getCustomAttribute('mp_sms_subscription')->getValue();
                $customer       = $this->helperSms->getCustomerById($customer->getId());
                if ($this->helperSms->allowSendBehaviorSubscription($storeId, 'subscribe_subscription', $recipient, $isSmsSubscribe)) {
                    $message = $this->helperSms->generateMessageContent(
                        $customer->getData(),
                        $this->helperSms->getSubscription($storeId)
                    );
                }
                if (isset($message) && $recipient) {
                    $this->smsFactory->create($this->helperSms->getSmsProvider($storeId))->send($message, $recipient);
                }
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

    }
}
