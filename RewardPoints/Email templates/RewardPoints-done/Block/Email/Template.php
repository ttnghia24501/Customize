<?php


namespace Mageplaza\RewardPoints\Block\Email;

use Magento\Framework\View\Element\Template as AbstractTemplate;
use Magento\Sales\Model\Order;
use Mageplaza\RewardPoints\Model\Transaction;
use Magento\Catalog\Block\Product\Context;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Account;
use Magento\Customer\Model\CustomerFactory;

/**
 * Class Template
 * @package Mageplaza\RewardPoints\Block\Email
 */
class Template extends AbstractTemplate
{
    protected $orderModel;
    protected $transaction;
    protected $helperData;
    protected $account;
    protected $customerFactory;

    public function __construct(
        Context $context,
        Order $orderModel,
        Transaction $transaction,
        Data $helperData,
        Account $account,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->orderModel      = $orderModel;
        $this->transaction     = $transaction;
        $this->helperData      = $helperData;
        $this->account         = $account;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    public function getOrder()
    {
        if ($orderId = $this->getOrderId()) {
            return $this->orderModel->load($orderId);
        }

        return null;
    }

    public function getIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    public function getProductList()
    {
        $items = [];
        if ($orderId = $this->getOrderId()) {
            return $this->orderModel->getAllItems();
        }

        return $items;
    }

    public function getPointAmount()
    {
        return $this->getOrder()->getData('mp_reward_earn');
    }

    public function getBalance()
    {
        $customerId = $this->getOrder()->getCustomerId();
        return $this->account->loadByCustomerId($customerId)->getBalance();
    }
}
