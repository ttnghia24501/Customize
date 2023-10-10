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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Data
 * @package Mageplaza\Customize\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH                 = 'mp_customize';
    const XML_PATH_SUBSCRIPTION_EMAIL_TYPE   = 'mp_customize_general_sub_template';
    const XML_PATH_UNSUBSCRIPTION_EMAIL_TYPE = 'mp_customize_general_unsub_template';
    const XML_PATH_NEW_CUSTOMER_EMAIL_TYPE   = 'mp_customize_general_new_customer_template';

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
     * Data constructor.
     *
     * @param TransportBuilder $transportBuilder
     * @param UrlInterface $backendUrl
     * @param CustomerRegistry $customerRegistry
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        UrlInterface $backendUrl,
        CustomerRegistry $customerRegistry,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->backendUrl       = $backendUrl;
        $this->customerRegistry = $customerRegistry;
        $this->customerSession  = $customerSession;
        $this->storeManager     = $storeManager;
        $this->scopeConfig      = $scopeConfig;
    }

    /**
     * @param $customerId
     *
     * @return CustomerInterface
     * @throws NoSuchEntityException
     */
    public function getCustomerById($customerId)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);

        return $customerModel->getDataModel();
    }

    /**
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
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
     * @param null $storeId
     *
     * @return array
     */
    public function getSendTo($storeId = null)
    {
        return array_map('trim', explode(',', (string) $this->getConfigGeneral('to', $storeId)));
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isSendByCron($storeId = null)
    {
        return $this->getModuleConfig('cron_job/enabled_cron', $storeId);
    }

    /**
     * @param $sendTo
     * @param $customer
     * @param $emailTemplate
     * @param $storeId
     * @param $sender
     *
     * @return bool
     */
    public function sendMail($sendTo, $customer, $emailTemplate, $storeId, $sender)
    {
        try {
            /** @var Customer $mergedCustomerData */
            $customerEmailData = $this->customerRegistry->retrieve($customer->getId());
            $customerEmailData->setData('name', $customerEmailData->getName());
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'customer' => $customerEmailData
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
}
