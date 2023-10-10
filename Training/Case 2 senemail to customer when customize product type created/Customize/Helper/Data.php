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

namespace Mageplaza\Customize\Helper;

use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;

/**
 * Class Data
 * @package Mageplaza\Customize\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH              = 'mp_customize';
    const XML_PATH_NEW_PRODUCT_EMAIL_TYPE = 'mp_customize_general_new_product_template';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @param TransportBuilder $transportBuilder
     * @param UrlInterface $backendUrl
     * @param CustomerRegistry $customerRegistry
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        UrlInterface $backendUrl,
        CustomerRegistry $customerRegistry,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        SubscriberFactory $subscriberFactory
    ) {
        $this->transportBuilder  = $transportBuilder;
        $this->backendUrl        = $backendUrl;
        $this->customerRegistry  = $customerRegistry;
        $this->customerSession   = $customerSession;
        $this->storeManager      = $storeManager;
        $this->scopeConfig       = $scopeConfig;
        $this->subscriberFactory = $subscriberFactory;
    }


    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getSender($storeId = null)
    {
        return $this->getConfigGeneral('sender', $storeId);
    }

    /**
     * @param $sendTo
     * @param $product
     * @param $emailTemplate
     * @param $storeId
     * @param $sender
     *
     * @return bool
     */
    public function sendMail($sendTo, $item, $emailTemplate, $storeId, $sender)
    {
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'item' => $item
                ])
                ->setFromByScope($sender, $storeId)
                ->addTo($sendTo)
                ->getTransport();
            $transport->sendMessage();

            return true;
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return false;
    }

    /**
     * @return array
     */
    public function getSubscribedCustomerEmails()
    {
        $subscribedEmails = [];

        $subscribers = $this->subscriberFactory->create()->getCollection()
            ->addFieldToFilter('subscriber_status', Subscriber::STATUS_SUBSCRIBED);

        foreach ($subscribers as $subscriber) {
            $subscribedEmails[] = $subscriber->getEmail();
        }

        return $subscribedEmails;
    }
}
