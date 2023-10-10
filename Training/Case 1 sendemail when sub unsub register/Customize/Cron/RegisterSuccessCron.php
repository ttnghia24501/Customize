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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Customize\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class SendEmail
 * @package Mageplaza\Customize\Cron
 */
class RegisterSuccessCron
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
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Customer
     */
    protected $customerResource;

    /**
     * RegisterSuccessCron constructor.
     *
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SortOrderBuilder $sortOrderBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param Customer $customerResource
     */
    public function __construct(
        Data $helperData,
        LoggerInterface $logger,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SortOrderBuilder $sortOrderBuilder,
        CustomerRepositoryInterface $customerRepository,
        Customer $customerResource
    ) {
        $this->helperData                   = $helperData;
        $this->logger                       = $logger;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sortOrderBuilder             = $sortOrderBuilder;
        $this->customerRepository           = $customerRepository;
        $this->customerResource             = $customerResource;
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $storeId = $this->helperData->getStoreId();
        $sendTo  = $this->helperData->getSendTo($storeId);
        if ($this->helperData->isEnabled($storeId) && $this->helperData->isSendByCron($storeId)) {
            try {
                $searchCriteria = $this->searchCriteriaBuilderFactory->create();
                $searchCriteria->addFilter('mp_email_register_success_sent', null, 'null');

                $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDirection('ASC')->create();
                $searchCriteria->setSortOrders([$sortOrder]);

                $customers  = $this->customerRepository->getList($searchCriteria->create())->getItems();
                $connection = $this->customerResource->getConnection();
                $tableName  = $this->customerResource->getTable('customer_entity');

                foreach ($customers as $customer) {
                    $sendTo[] = $customer->getEmail();

                    $this->helperData->sendMail(
                        $sendTo,
                        $customer,
                        Data::XML_PATH_NEW_CUSTOMER_EMAIL_TYPE,
                        $storeId,
                        $this->helperData->getSender($storeId)
                    );

                    $updateData = [
                        'mp_email_register_success_sent' => 1,
                    ];
                    $where      = ['entity_id = ?' => $customer->getId()];
                    $connection->update($tableName, $updateData, $where);
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
