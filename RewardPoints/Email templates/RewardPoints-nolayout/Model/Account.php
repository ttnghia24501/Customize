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

namespace Mageplaza\RewardPoints\Model;

use Exception;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\RewardPoints\Api\Data\RewardCustomerInterface;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Model\ResourceModel\Account as ResourceAccount;
use Mageplaza\RewardPoints\Model\Source\ActionType;

/**
 * Class Account
 * @method ResourceModel\Account getResource()
 * @package Mageplaza\RewardPoints\Model
 */
class Account extends AbstractExtensibleModel implements IdentityInterface, RewardCustomerInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_rewardpoints_account';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_account';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Account constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param HelperData $helperData
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HelperData $helperData,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceAccount::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $customerId
     *
     * @return $this
     */
    public function loadByCustomerId($customerId)
    {
        return $this->load($customerId, 'customer_id');
    }

    /**
     * @param string $storeId
     * @param array $data
     *
     * @return $this
     * @throws Exception
     */
    public function create($storeId, $data = [])
    {
        $subscribeDefault = $this->helperData->getEmailHelper()->getEmailConfig(
            'subscribe_by_default',
            $storeId
        );
        $this->addData(array_merge([
            'notification_expire' => $subscribeDefault,
            'notification_update' => $subscribeDefault
        ], $data));

        $this->save();

        return $this;
    }

    /**
     * @param $balance
     */
    public function addBalance($balance)
    {
        $this->setBalance($this->getBalance() + $balance);
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->getData('point_balance');
    }

    /**
     * @param $amount
     */
    public function setBalance($amount)
    {
        $this->setData('point_balance', $amount);
    }

    /**
     * Get balance with point label
     *
     * @param null $storeId
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBalanceFormatted($storeId = null)
    {
        $balance = $this->getBalance();

        return $this->helperData->getPointHelper()->format($balance, false, $storeId);
    }

    /**
     * @param bool $format
     * @param null $storeId
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTotalEarningPoints($format = false, $storeId = null)
    {
        $earningPoints = $this->getResource()->getTotalPointsByType($this, ActionType::EARNING);

        return $format ? $this->helperData->getPointHelper()->format($earningPoints, false, $storeId) : $earningPoints;
    }

    /**
     * @param bool $format
     * @param null $storeId
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTotalSpendingPoints($format = false, $storeId = null)
    {
        $spendingPoints = abs($this->getResource()->getTotalPointsByType($this, ActionType::SPENDING) ?: 0);

        return $format ? $this->helperData->getPointHelper()->format($spendingPoints, false, $storeId)
            : $spendingPoints;
    }

    /**
     * {@inheritdoc}
     */
    public function getRewardId()
    {
        return $this->getData(self::REWARD_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRewardId($value)
    {
        return $this->setData(self::REWARD_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($value)
    {
        return $this->setData(self::CUSTOMER_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointBalance()
    {
        return $this->getData(self::POINT_BALANCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointBalance($value)
    {
        return $this->setData(self::POINT_BALANCE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointSpent()
    {
        $this->setId($this->getData(self::REWARD_ID));

        return $this->getTotalSpendingPoints();
    }

    /**
     * {@inheritdoc}
     */
    public function setPointSpent($value)
    {
        return $this->setData(self::POINT_SPENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointEarned($value)
    {
        return $this->setData(self::POINT_EARNED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointEarned()
    {
        return $this->getTotalEarningPoints() ?: 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationUpdate()
    {
        return $this->getData(self::NOTIFICATION_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setNotificationUpdate($value)
    {
        return $this->setData(self::NOTIFICATION_UPDATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationExpire()
    {
        return $this->getData(self::NOTIFICATION_EXPIRE);
    }

    /**
     * {@inheritdoc}
     */
    public function setNotificationExpire($value)
    {
        return $this->setData(self::NOTIFICATION_EXPIRE, $value);
    }
}
