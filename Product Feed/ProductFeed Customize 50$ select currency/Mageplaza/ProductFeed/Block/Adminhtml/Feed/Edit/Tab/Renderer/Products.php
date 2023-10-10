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

namespace Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\ProductFeed\Model\FeedFactory;

/**
 * Class Products
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer
 */
class Products extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var FeedFactory
     */
    protected $_feedFactory;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * Products constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param FeedFactory $feedFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Session $backendSession
     * @param array $data
     */
    public function __construct(
        Context           $context,
        Data              $backendHelper,
        FeedFactory       $feedFactory,
        CollectionFactory $productCollectionFactory,
        Session           $backendSession,
        array             $data = []
    ) {
        $this->_feedFactory              = $feedFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->backendSession            = $backendSession;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/products');
    }

    /**
     * @param object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }

    /**
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        if ($rule = $this->_request->getParam('rule')) {
            $this->backendSession->setRule($rule);
        }

        if ($feed = $this->_request->getParam('feed')) {
            $this->backendSession->setFeed($feed);
        }

        if ($this->backendSession->getRule()) {
            $this->_request->setParam('rule', $this->backendSession->getRule());
        }

        if ($this->backendSession->getFeed()) {
            $this->_request->setParam('feed', $this->backendSession->getFeed());
        }

        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addFieldToFilter('entity_id', ['in' => $this->_getSelectedProducts()]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return array|null
     * @throws LocalizedException
     */
    protected function _getSelectedProducts()
    {
        return $this->_feedFactory->create()->getMatchingProductIds();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header'           => __('Product ID'),
            'type'             => 'number',
            'index'            => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('name', [
            'header' => __('Name'),
            'index'  => 'name',
            'class'  => 'xxx',
            'width'  => '50px',
        ]);
        $this->addColumn('sku', [
            'header' => __('Sku'),
            'index'  => 'sku',
            'class'  => 'xxx',
            'width'  => '50px',
        ]);
        $this->addColumn('price', [
            'header' => __('Price'),
            'type'   => 'currency',
            'index'  => 'price',
            'width'  => '50px',
        ]);

        return parent::_prepareColumns();
    }
}
