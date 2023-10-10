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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Customer\Edit\Tab\Grid;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Store\Model\System\Store;
use Mageplaza\RewardPoints\Model\ActionFactory;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\Transaction;

/**
 * Class History
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Customer\Edit\Tab\Grid
 */
class History extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * History constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param Store $systemStore
     * @param CollectionFactory $transactionCollectionFactory
     * @param ActionFactory $actionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Store $systemStore,
        CollectionFactory $transactionCollectionFactory,
        ActionFactory $actionFactory,
        array $data = []
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->systemStore = $systemStore;
        $this->actionFactory = $actionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('rewardTransactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoadCollection()
    {
        /** @var Transaction $transaction */
        foreach ($this->getCollection() as $transaction) {
            $transaction->addTitle();
        }

        return $this;
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', [
            'header' => __('ID'),
            'index' => 'transaction_id',
            'sortable' => true,
            'type' => 'number'
        ]);
        $this->addColumn('title', [
            'header' => __('Title'),
            'index' => 'title',
            'type' => 'text',
            'sortable' => false,
            'filter' => false
        ]);
        $this->addColumn('action_code', [
            'header' => __('Action'),
            'index' => 'action_code',
            'type' => 'options',
            'options' => $this->actionFactory->getOptionHash()
        ]);
        $this->addColumn('point_amount', [
            'header' => __('Amount'),
            'index' => 'point_amount',
            'type' => 'number',
        ]);
        $this->addColumn('status', [
            'header' => __('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Status::getOptionArray()
        ]);
        $this->addColumn('store_id', [
            'header' => __('Store View'),
            'index' => 'store_id',
            'type' => 'options',
            'options' => $this->systemStore->getStoreOptionHash(true),
        ]);
        $this->addColumn('created_at', [
            'header' => __('Created On'),
            'index' => 'created_at',
            'type' => 'datetime',
        ]);
        $this->addColumn('expiration_date', [
            'header' => __('Expire On'),
            'index' => 'expiration_date',
            'type' => 'datetime',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpreward/transaction/historygrid', ['_current' => true]);
    }
}
