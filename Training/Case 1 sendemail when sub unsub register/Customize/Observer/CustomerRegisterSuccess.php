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
use Mageplaza\Customize\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerRegisterSuccess
 *
 * @package Mageplaza\Customize\Observer
 */
class CustomerRegisterSuccess implements ObserverInterface
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
     * CustomerRegisterSuccess constructor.
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

    public function execute(Observer $observer)
    {
        $customer      = $observer->getCustomer();
        $customerEmail = $customer->getEmail();
        $customerId    = $customer->getId();
        $storeId       = $this->helperData->getStoreId();
        $sendTo        = $this->helperData->getSendTo($storeId);
        $sendTo[]      = $customerEmail;

        if ($customerId) {
            $customer = $this->helperData->getCustomerById($customerId);
        } else {
            $customer = $this->helperData->getCustomerSession();
        }
        if ($this->helperData->isEnabled($storeId) && !$this->helperData->isSendByCron($storeId)) {
            try {
                $this->helperData->sendMail($sendTo, $customer, Data::XML_PATH_NEW_CUSTOMER_EMAIL_TYPE,
                    $storeId, $this->helperData->getSender($storeId));
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
