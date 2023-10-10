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

namespace Mageplaza\RewardPoints\Plugin;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\AccountFactory;
use Psr\Log\LoggerInterface;

/**
 * Class CreateRewardAccount
 * @package Mageplaza\RewardPoints\Plugin
 */
class CreateRewardAccount
{
    /**
     * @var AccountFactory
     */
    protected $account;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * CreateRewardAccount constructor.
     *
     * @param AccountFactory $account
     * @param LoggerInterface $logger
     * @param Data $helperData
     */
    public function __construct(
        AccountFactory $account,
        LoggerInterface $logger,
        Data $helperData
    ) {
        $this->account = $account;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     *
     * @return CustomerInterface
     */
    public function afterCreateAccountWithPasswordHash(
        AccountManagement $subject,
        CustomerInterface $customer
    ) {
        if ($this->helperData->isActionImport()) {
            return $customer;
        }

        try {
            $this->account->create()->create($customer->getStoreId(), ['customer_id' => $customer->getId()]);
        } catch (Exception $e) {
            /**
             * Allow to register customer and log reward exception
             */
            $this->logger->critical($e);
        }

        return $customer;
    }
}
