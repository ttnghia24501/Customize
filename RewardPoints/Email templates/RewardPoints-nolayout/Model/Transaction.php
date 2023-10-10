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
use IntlDateFormatter;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\RewardPoints\Api\Data\TransactionInterface;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Helper\Email;
use Mageplaza\RewardPoints\Model\Action\ActionInterface;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction as ResourceTransaction;
use Mageplaza\RewardPoints\Model\Source\Status;
use Magento\Sales\Model\Order;

/**
 * Class Transaction
 * @method int getPointAmountUpdated()
 * @method $this setPointAmountUpdated($amount)
 */
class Transaction extends AbstractExtensibleModel implements IdentityInterface, TransactionInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_rewardpoints_transaction';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_transaction';

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var helperData
     */
    protected $helperData;

    /**
     * @var ActionInterface[]
     */
    protected $actionByCode = [];

    /**
     * Transaction constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param HelperData $helperData
     * @param ActionFactory $actionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        HelperData $helperData,
        ActionFactory $actionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->actionFactory = $actionFactory;
        $this->helperData    = $helperData;

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
        $this->_init(ResourceTransaction::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $code
     * @param $customer
     * @param $actionObject
     *
     * @return $this
     * @throws LocalizedException
     */
    public function createTransaction($code, $customer, $actionObject)
    {
        /** @var Action $action */
        $action          = $this->getActionModel($code, ['customer' => $customer, 'actionObject' => $actionObject]);
        $transactionData = $action->prepareTransaction();

        if (!is_array($transactionData)) {
            throw new LocalizedException(__('Invalid transaction Data'));
        }

        $transactionData['action_code'] = $code;
        $this->setData($transactionData);

        /** @var Account $account */
        $account = $this->getAccount($customer->getId());
        if (!$account->getId()) {
            throw new LocalizedException(__('Reward account does not exist'));
        }

        if ($account->getBalance() + $this->getPointAmount() < 0) {
            throw new LocalizedException(__('Account balance is not enough to take points back.'));
        }

        $this->setData('reward_id', $account->getId());
        if ($this->getPointAmount() > 0) {
            $this->setData('point_remaining', $this->getPointAmount());
        }

        $sendEmailUpdate = 0;
        if ((int) $this->getStatus() === Status::COMPLETED) {
            $maxBalance = $this->helperData->getMaxPointPerCustomer($this->getStoreId());
            if ($code === HelperData::ACTION_ADMIN || $code === HelperData::ACTION_SPENDING_REFUND) {
                $account->addBalance($this->getPointAmount());
            } else {
                if ($maxBalance > 0 && $this->getPointAmount() > 0
                    && ($account->getBalance() + $this->getPointAmount() > $maxBalance)) {
                    $availableAmount = $maxBalance - $account->getBalance();
                    if ($availableAmount <= 0) {
                        return $this;
                    }

                    $this->setPointAmount($availableAmount);
                    $account->setBalance($maxBalance);
                } else {
                    $account->addBalance($this->getPointAmount());
                }
            }

            $sendEmailUpdate = 1;
        }

        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (Exception $e) {
            throw new LocalizedException(__('An error occurred while creating transaction. Please try again later.'));
        }

        if ($sendEmailUpdate) {
            if ($transactionData['order_id']) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order         = $objectManager->get('Magento\Sales\Model\Order')->load($transactionData['order_id']);
            }
            $this->sendUpdateBalanceEmail($order);
        }

        if ($this->getPointAmount() < 0) {
            $actionType = $this->getData('action_type');
            if ((int) $actionType === Data::ACTION_TYPE_EARNING) {
                $this->getResource()->updatePointRemaining($this);
            } elseif ((int) $actionType === Data::ACTION_TYPE_SPENDING) {
                $this->getResource()->updatePointUsed($this);
            }
        }

        $this->_eventManager->dispatch($this->_eventPrefix . '_created', $this->_getEventData());
        $this->_eventManager->dispatch($this->_eventPrefix . '_created_' . $code, $this->_getEventData());

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function complete()
    {
        if (!$this->canComplete()) {
            throw new LocalizedException(__('Invalid transaction data to complete.'));
        }

        $account    = $this->getAccount();
        $maxBalance = $this->helperData->getMaxPointPerCustomer($this->getStoreId());

        if ($maxBalance > 0
            && $this->getPointAmount() > 0 && ($account->getBalance() + $this->getPointAmount() > $maxBalance)) {
            throw new LocalizedException(__(
                'Cannot complete this transaction. Maximum points allowed in account balance is %1',
                $maxBalance
            ));
        }

        $account->addBalance($this->getPointRemaining());
        $this->setStatus(Status::COMPLETED);

        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (Exception $e) {
            throw new LocalizedException(__('An error occurred while completing transaction. Please try again later.'));
        }

        $this->sendUpdateBalanceEmail();

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException | Exception
     */
    public function cancel()
    {
        if (!$this->canCancel()) {
            throw new LocalizedException(__('Invalid transaction data to cancel.'));
        }

        $account = $this->getAccount();
        if ($account->getBalance() < $this->getPointRemaining()) {
            throw new LocalizedException(__('Account balance is not enough to cancel.'));
        }

        $account->addBalance(-$this->getPointRemaining());
        $this->setStatus(Status::CANCELED);

        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (Exception $e) {
            throw new LocalizedException(__('An error occurred while canceling transaction. Please try again later.'));
        }

        $this->sendUpdateBalanceEmail();

        /**
         * When canceling transaction, we need to move point_used to other transaction because of invalid point_used in
         * this transaction
         */
        if ($this->getPointUsed() > 0) {
            $this->setPointAmountUpdated(-$this->getPointUsed());
            $this->getResource()->updatePointUsed($this);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function expire()
    {
        if (!$this->canExpire()) {
            return $this;
        }

        $account      = $this->getAccount();
        $pointExpired = $this->getPointRemaining() - $this->getPointUsed();
        if ($account->getBalance() == 0) {
            $account->addBalance(0);
        } elseif ($account->getBalance() - $pointExpired < 0) {
            $account->addBalance(-1.0 * $account->getBalance());
        } else {
            $account->addBalance(-1.0 * ($this->getPointRemaining() - $this->getPointUsed()));
        }
        $this->setStatus(Status::EXPIRED);
        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (Exception $e) {
            throw new LocalizedException(__('An error occurred while expiring transaction. Please try again later.'));
        }

        $this->sendUpdateBalanceEmail();

        return $this;
    }

    /**
     * @return bool
     */
    public function canComplete()
    {
        return $this->getId()
            && $this->getPointAmount() > 0
            && $this->getStatus() < Status::COMPLETED;
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        return $this->getId()
            && $this->getPointAmount() > 0
            && $this->getStatus() < Status::CANCELED;
    }

    /**
     * @return bool
     */
    public function canExpire()
    {
        return $this->getId()
            && ($this->getPointRemaining() > $this->getPointUsed())
            && ($this->getStatus() <= Status::COMPLETED)
            && $this->getExpirationDate()
            && (strtotime($this->getExpirationDate()) <= time());
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        $statusArray = Status::getOptionArray();
        if (array_key_exists($this->getStatus(), $statusArray)) {
            $status = $statusArray[$this->getStatus()];
            if ($status instanceof Phrase) {
                return $status->__toString();
            }

            return $status;
        }

        return '';
    }

    /**
     * @param $code
     * @param array $data
     *
     * @return ActionInterface
     */
    protected function getActionModel($code, $data = [])
    {
        if (!isset($this->actionByCode[$code])) {
            $this->actionByCode[$code] = $this->actionFactory->create($code, $data);
        }

        return $this->actionByCode[$code];
    }

    /**
     * @param $code
     *
     * @return ActionInterface
     */
    public function getActionLabel($code)
    {
        return $this->getActionModel($code)->getActionLabel();
    }

    /**
     * @return $this
     */
    public function addTitle()
    {
        $this->setData('title', $this->getTitle());

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        try {
            $action = $this->getActionModel($this->getActionCode() ?: HelperData::ACTION_ADMIN);

            return $action->getTitle($this);
        } catch (\Exception $e) {
            return '';
        }

    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function sendUpdateBalanceEmail($order = null)
    {
        $storeId = $this->getStoreId();

        $this->helperData->getEmailHelper()->sendEmailTemplate(
            $this->getCustomerId(),
            Email::XML_PATH_UPDATE_TRANSACTION_EMAIL_TYPE,
            $this->getEmailParams($order),
            $storeId
        );

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function sendExpiredTransactionEmail()
    {
        $this->helperData->getEmailHelper()->sendEmailTemplate(
            $this->getCustomerId(),
            Email::XML_PATH_EXPIRE_EMAIL_TYPE,
            $this->getEmailParams()
        );

        try {
            $this->setData('expire_email_sent', 1)
                ->save();
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $this;
    }

    /**
     * @param Order $order
     *
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEmailParams($order = null)
    {
        $customer          = $this->helperData->getAccountHelper()->getCustomerById($this->getCustomerId());
        $customerName      = $customer->getName();
        $customerEmail     = $customer->getEmail();
        $customerTelephone = $customer->getPrimaryBillingAddress()->getTelephone();
        $orderId           = $order->getId();
        $items             = $order->getAllItems();
        $orderStatus       = $order->getStatus();
        $productName       = '';
        $productQty        = '';
        foreach ($items as $item) {
            $productName .= $item->getName() . ',';
            $productQty  .= round($item->getQtyOrdered()) . ',';
        }
        $productName = rtrim($productName, ',');
        $productQty = rtrim($productQty, ',');

        $params      = [
            'customer_name'           => $customerName,
            'point_amount'            => $this->getPointAmount(),
            'point_amount_formatted'  => $this->helperData->getPointHelper()->format(
                $this->getPointAmount(),
                false,
                $this->getStoreId()
            ),
            'status'                  => $this->getStatusLabel(),
            'point_balance'           => $this->getAccount()->getBalance(),
            'point_balance_formatted' => $this->getAccount()->getBalanceFormatted($this->getStoreId()),
            'expiration_date'         => $this->getExpirationDate() ? $this->helperData->formatDate(
                $this->getExpirationDate(),
                IntlDateFormatter::MEDIUM,
                true
            ) : '',
            'comment'                 => $this->getTitle(),
            'customer_email'          => $customerEmail,
            'customer_telephone'      => $customerTelephone,
            'order_id'                => $orderId,
            'order_status'            => $orderStatus,
            'product_name'            => $productName,
            'product_qty'             => $productQty
        ];

        return $params;
    }

    /**
     * @param null $customerId
     *
     * @return Account|mixed
     */
    protected function getAccount($customerId = null)
    {
        $accountHelper = $this->helperData->getAccountHelper();

        return $customerId ? $accountHelper->create($customerId) : $accountHelper->get($this->getRewardId());
    }

    /**
     * @param $customerId
     *
     * @return AbstractCollection
     */
    public function getTransactionInFrontend($customerId)
    {
        $collection = $this->getCollection()->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('created_at', 'DESC');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($value)
    {
        return $this->setData(self::TRANSACTION_ID, $value);
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
    public function getActionCode()
    {
        return $this->getData(self::ACTION_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionCode($value)
    {
        return $this->setData(self::ACTION_CODE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionType()
    {
        return $this->getData(self::ACTION_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionType($value)
    {
        return $this->setData(self::ACTION_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($value)
    {
        return $this->setData(self::STORE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointAmount()
    {
        return $this->getData(self::POINT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointAmount($value)
    {
        return $this->setData(self::POINT_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointRemaining()
    {
        return $this->getData(self::POINT_REMAINING);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointRemaining($value)
    {
        return $this->setData(self::POINT_REMAINING, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointUsed()
    {
        return $this->getData(self::POINT_USED);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointUsed($value)
    {
        return $this->setData(self::POINT_USED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($value)
    {
        return $this->setData(self::ORDER_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate()
    {
        return $this->getData(self::EXPIRATION_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpirationDate($value)
    {
        return $this->setData(self::EXPIRATION_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpireEmailSent()
    {
        return $this->getData(self::EXPIRE_EMAIL_SENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpireEmailSent($value)
    {
        return $this->setData(self::EXPIRE_EMAIL_SENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraContent()
    {
        return $this->getData(self::EXTRA_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtraContent($value)
    {
        return $this->setData(self::EXTRA_CONTENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT) ?: $this->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($value)
    {
        return $this->setData(self::COMMENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpireAfter()
    {
        return $this->getData(self::EXPIRE_AFTER);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpireAfter($value)
    {
        return $this->setData(self::EXPIRE_AFTER, $value);
    }
}
