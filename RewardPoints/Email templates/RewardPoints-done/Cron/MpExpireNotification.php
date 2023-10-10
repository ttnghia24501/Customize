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

namespace Mageplaza\RewardPoints\Cron;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\Transaction;
use Mageplaza\RewardPoints\Model\TransactionFactory;

/**
 * Class MpExpireNotification
 * @package Mageplaza\RewardPoints\Cron
 */
class MpExpireNotification
{
    /**
     * Core model store manager interface
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var TransactionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * MpExpireNotification constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $transactionCollectionFactory
     * @param DateTime $dateTime
     * @param HelperData $helperData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $transactionCollectionFactory,
        DateTime $dateTime,
        HelperData $helperData
    ) {
        $this->_storeManager = $storeManager;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->dateTime = $dateTime;
        $this->helperData = $helperData;
    }

    /**
     * Send scheduled warning notifications
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        foreach ($this->_storeManager->getStores() as $store) {
            $beforeExpireDays = $this->helperData->getEmailHelper()->getEmailConfig(
                'expire/before_days',
                $store->getId()
            );
            if ($this->helperData->isEnabled($store->getId()) && $beforeExpireDays) {
                $transactionCollection = $this->transactionCollectionFactory->create()
                    ->addFieldToFilter('status', Status::COMPLETED)
                    ->addFieldToFilter('expiration_date', ['notnull' => true])
                    ->addFieldToFilter('expire_email_sent', 0)
                    ->addFieldToFilter('expiration_date', [
                        'to' => date('Y-m-d H:i:s', strtotime($this->dateTime->gmtDate()) + $beforeExpireDays * 86400)
                    ])
                    ->addFieldToFilter('expiration_date', ['from' => $this->dateTime->gmtDate()]);

                if ($transactionCollection->getSize() > 0) {
                    /** @var Transaction $transaction */
                    foreach ($transactionCollection as $transaction) {
                        $transaction->sendExpiredTransactionEmail();
                    }
                }
            }
        }

        return $this;
    }
}
