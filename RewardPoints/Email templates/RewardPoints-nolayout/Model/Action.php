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

use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Action\ActionInterface;
use Mageplaza\RewardPoints\Model\Source\Status;

/**
 * Class Action
 * @package Mageplaza\RewardPoints\Model
 */
abstract class Action extends DataObject implements ActionInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var null
     */
    protected $customer;

    /**
     * @var null
     */
    protected $actionObject;

    /**
     * Action constructor.
     *
     * @param Data $helper
     * @param null $customer
     * @param null $actionObject
     * @param array $data
     */
    public function __construct(
        Data $helper,
        $customer = null,
        $actionObject = null,
        array $data = []
    ) {
        $this->helper       = $helper;
        $this->customer     = $customer;
        $this->actionObject = $actionObject;

        parent::__construct($data);
    }

    /**
     * @inheritdoc
     */
    public function prepareTransaction()
    {
        $customer        = $this->getCustomer();
        $transactionData = [
            'customer_id'     => $customer->getId(),
            'customer_email'  => $customer->getEmail(),
            'action_type'     => $this->getActionType(),
            'point_amount'    => $this->getPointAmount(),
            'order_id'        => $this->getActionObject()->getData('order_id'),
            'status'          => $this->getStatus(),
            'store_id'        => $this->getStoreId(),
            'expiration_date' => $this->getExpirationDate(),
            'extra_content'   => Data::jsonEncode($this->getExtraContent())
        ];

        return $transactionData;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getPointAmount()
    {
        $pointAmount = $this->getActionObject()->getData('point_amount') ?: 0;
        if ($pointAmount !== 0 && !abs($pointAmount)) {
            throw new LocalizedException(__('Cannot create transaction. Point amount is invalid.'));
        }

        return (int) $pointAmount;
    }

    /**
     * @return int
     */
    protected function getStatus()
    {
        return Status::COMPLETED;
    }

    /**
     * @param $transaction
     *
     * @return string
     */
    public function getOrderIncrementId($transaction)
    {
        $extraContent = Data::jsonDecode($transaction->getData('extra_content'));
        if (isset($extraContent['increment_id']) && !empty($extraContent['increment_id'])) {
            return $extraContent['increment_id'];
        }

        return '';
    }

    /**
     * @param Transaction $transaction
     * @param string $comment
     *
     * @return Phrase|string
     */
    public function getComment($transaction, $comment)
    {
        if ($incrementId = $this->getOrderIncrementId($transaction)) {
            return __($comment, $incrementId);
        }

        return '';
    }

    /**
     * @return null
     */
    protected function getExpirationDate()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function getExtraContent()
    {
        return $this->getActionObject()->getData('extra_content') ?: [];
    }

    /**
     * @return Customer|null
     */
    protected function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        $actionObject = $this->getActionObject();
        $customer     = $this->getCustomer();

        if ($actionObject->getData('store_id')) {
            return $actionObject->getData('store_id');
        }
        if (method_exists($customer, 'getData')) {
            return $customer->getData('store_id');
        } else {
            return $customer->getStoreId();
        }
    }

    /**
     * @return DataObject|null
     */
    protected function getActionObject()
    {
        return $this->actionObject;
    }
}
