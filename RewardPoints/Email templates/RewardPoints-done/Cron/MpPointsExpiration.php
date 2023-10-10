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

use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\Transaction;

/**
 * Class MpPointsExpiration
 * @package Mageplaza\RewardPoints\Cron
 */
class MpPointsExpiration
{
    /**
     * Core model store manager interface
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CollectionFactory
     */
    protected $transCollectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * MpPointsExpiration constructor.
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
        $this->transCollectionFactory = $transactionCollectionFactory;
        $this->dateTime = $dateTime;
        $this->helperData = $helperData;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function execute()
    {
        foreach ($this->_storeManager->getStores() as $store) {
            if ($this->helperData->isEnabled($store->getId())) {
                $transactionCollection = $this->transCollectionFactory->create()
                    ->addFieldToFilter('status', Status::COMPLETED)
                    ->addFieldToFilter('expiration_date', ['notnull' => true])
                    ->addFieldToFilter('store_id', $store->getId())
                    ->addFieldToFilter('expiration_date', ['lteq' => $this->dateTime->gmtDate()]);

                /** @var Transaction $transaction */
                foreach ($transactionCollection as $transaction) {
                    $transaction->expire();
                }
            }
        }

        return $this;
    }
}
