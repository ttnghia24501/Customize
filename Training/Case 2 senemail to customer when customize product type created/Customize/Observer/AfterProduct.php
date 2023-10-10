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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Customize\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 *  Class AfterProduct
 * @package Mageplaza\Customize\Observer
 */
class AfterProduct implements ObserverInterface
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
     * @param Data $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helperData,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->logger     = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getDataObject();

        try {
            $storeId = $this->helperData->getStoreId();
            $sender  = $this->helperData->getSender($storeId);
            if ($item->isObjectNew() && $this->helperData->isEnabled($storeId) && $item->getTypeId() === 'customize_product') {
                $sendTo = $this->helperData->getSubscribedCustomerEmails();
                $this->helperData->sendMail($sendTo, $item, Data::XML_PATH_NEW_PRODUCT_EMAIL_TYPE, $storeId, $sender);
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
