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

namespace Mageplaza\RewardPoints\Block\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Collection;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPoints\Model\TransactionFactory;

/**
 * Class Transaction
 * @method Collection getTransactions()
 * @method void setTransactions($transactions)
 * @package Mageplaza\RewardPoints\Block\Account
 */
class Transaction extends Dashboard
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TransactionFactory
     */
    protected $collectionFactory;

    /**
     * Transaction constructor.
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $helper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $transactions = $this->collectionFactory->create()
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
            ->setOrder('created_at', 'desc');

        $this->setTransactions($transactions);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getTransactions()) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'mpreward.transactions.pager')
                ->setCollection($this->getTransactions());
            $this->setChild('pager', $pager);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
