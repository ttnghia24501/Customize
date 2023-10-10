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

namespace Mageplaza\RewardPoints\Observer;

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

/**
 * Class RewardCustomerSaveAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class RewardCustomerSaveAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * RewardCustomerSaveAfter constructor.
     *
     * @param LoggerInterface $logger
     * @param HelperData $helperData
     */
    public function __construct(
        LoggerInterface $logger,
        HelperData $helperData
    ) {
        $this->logger     = $logger;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $request = $observer->getEvent()->getRequest();
        if (($data = $request->getPost('mpreward')) && isset($data['is_active'])) {
            $customer    = $observer->getEvent()->getCustomer();
            $accountData = [
                'notification_update' => !empty($data['notification_update']) ?: 0,
                'notification_expire' => !empty($data['notification_expire']) ?: 0
            ];
            if (isset($data['is_active'])) {
                $accountData['is_active'] = $data['is_active'];
            }
            try {
                $this->helperData->getAccountHelper()->create($customer, $accountData);
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }

            if (isset($data['point_amount']) && $data['point_amount'] !== 0
                && $data['point_amount'] !== '' && $data['is_active'] !== '0') {
                $this->helperData->getTransaction()
                    ->createTransaction(HelperData::ACTION_ADMIN, $customer, new DataObject($data));
            }
        }

        return $this;
    }
}
