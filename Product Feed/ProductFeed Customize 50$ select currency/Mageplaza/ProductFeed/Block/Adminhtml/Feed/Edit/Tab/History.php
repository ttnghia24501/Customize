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
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Helper\Data as HelperData;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\ResourceModel\History\Collection;
use Mageplaza\ProductFeed\Model\ResourceModel\History\CollectionFactory;

/**
 * Class History
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab
 */
class History extends Extended implements TabInterface
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * History constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $backendHelper
     * @param HelperData $helperData
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $backendHelper,
        HelperData $helperData,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('history_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $feed = $this->getFeed();
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        if ($feed && $feed->getId()) {
            $collection->addFieldToFilter('feed_id', $feed->getId());
        } else {
            $collection = false;
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('ID'),
            'sortable' => true,
            'index' => 'id',
            'type' => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);
        $this->addColumn('type', [
            'header' => __('Type'),
            'name' => 'type',
            'index' => 'type',
        ]);
        $this->addColumn('file', [
            'header' => __('File'),
            'name' => 'file',
            'index' => 'file',
        ]);
        $this->addColumn('status', [
            'header' => __('Status'),
            'name' => 'status',
            'index' => 'status',
            'type' => 'options',
            'options' => ['1' => 'Success', '0' => 'Error']
        ]);
        $this->addColumn('success_message', [
            'header' => __('Success Message'),
            'name' => 'success_message',
            'index' => 'success_message',
        ]);
        $this->addColumn('delivery', [
            'header' => __('Delivery'),
            'name' => 'delivery',
            'index' => 'delivery',
            'type' => 'options',
            'options' => ['1' => 'Success', '0' => 'Error', '2' => 'Disabled']
        ]);
        $this->addColumn('error_message', [
            'header' => __('Error Message'),
            'name' => 'error_message',
            'index' => 'error_message',
        ]);
        $this->addColumn('created_at', [
            'header' => __('Generation time'),
            'index' => 'created_at',
            'filter' => false,
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
        ]);

        return $this;
    }

    /**
     * @return History
     * @throws Exception
     */
    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection()->getItems() as $history) {
            $history->setData('created_at', $this->helperData->convertToLocaleTime($history->getCreatedAt()));
        }

        return parent::_afterLoadCollection(); // TODO: Change the autogenerated stub
    }

    /**
     * get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/log', ['feed_id' => $this->getFeed()->getFeedId()]);
    }

    /**
     * @return Feed
     */
    public function getFeed()
    {
        return $this->coreRegistry->registry('mageplaza_productfeed_feed');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Logs');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('mpproductfeed/logs/log', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
