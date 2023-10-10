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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Helper;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Model\Account as AccountModel;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\RewardPoints\Model\AccountFactory;

/**
 * Class Account
 * @package Mageplaza\RewardPoints\Helper
 */
class Account extends AbstractData
{
    /**
     * @var AccountModel[]
     */
    protected $accountById = [];

    /**
     * @var AccountModel[]
     */
    protected $accountByCustomerId = [];

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var HttpContext
     */
    protected $httpContext;
    /**
     * @var SessionFactory
     */
    private SessionFactory $sessionFactory;

    /**
     * Account constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param SessionFactory $sessionFactory
     * @param CustomerRegistry $customerRegistry
     * @param AccountFactory $accountFactory
     * @param HttpContext $httpContext
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        SessionFactory $sessionFactory,
        CustomerRegistry $customerRegistry,
        AccountFactory $accountFactory,
        HttpContext $httpContext
    ) {
        $this->customerSession  = $customerSession;
        $this->customerRegistry = $customerRegistry;
        $this->accountFactory   = $accountFactory;
        $this->httpContext      = $httpContext;
        $this->sessionFactory   = $sessionFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $accountId
     *
     * @return AccountModel
     */
    public function get($accountId = null)
    {
        if (!$accountId && $this->isCustomerLoggedIn()) {
            $customerId = $this->sessionFactory->create()->getId();

            return $this->getByCustomerId($customerId);
        }

        if (!isset($this->accountById[$accountId])) {
            $this->accountById[$accountId] = $this->accountFactory->create()->load($accountId);
        }

        return $this->accountById[$accountId];
    }

    /**
     * @param $customerId
     *
     * @return mixed
     */
    public function getByCustomerId($customerId)
    {
        if (!isset($this->accountByCustomerId[$customerId])) {
            $this->accountByCustomerId[$customerId] = $this->create($customerId);
        }

        return $this->accountByCustomerId[$customerId];
    }

    /**
     * @param CustomerInterface|int $customer
     * @param array $data
     *
     * @return mixed
     */
    public function create($customer, $data = [])
    {
        if (!$customer instanceof CustomerInterface) {
            $customer = $this->getCustomerById($customer);
        }

        /** @var AccountModel $account */
        $account = $this->accountFactory->create();
        $account->loadByCustomerId($customer->getId());

        try {
            if ($account->getId()) {
                $account->addData($data)
                    ->save();
            } else {
                $subscribeDefault = $this->objectManager->get(Email::class)
                    ->getEmailConfig('subscribe_by_default', $customer->getStoreId());

                $account->setCustomerId($customer->getId())
                    ->addData(array_merge([
                        'notification_expire' => $subscribeDefault,
                        'notification_update' => $subscribeDefault
                    ], $data))
                    ->save();
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $account;
    }

    /**
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @param int $customerId
     *
     * @return Customer
     */
    public function getCustomerById($customerId)
    {
        try {
            return $this->customerRegistry->retrieve($customerId);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->_request->isAjax() ? $this->getCustomerSession()->isLoggedIn()
            : (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerGroupId()
    {
        return $this->customerSession->getCustomerGroupId();
    }
}
