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
 * @category  Mageplaza
 * @package   Mageplaza_BetterWishlist
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Block\Adminhtml\Customer\Edit\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty;
use Magento\Sales\Model\Config as SalesConfig;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class ProductsGrid
 *
 * @package Mageplaza\BetterWishlist\Block\Adminhtml\Customer\Edit\Tab
 * @method  setId(string $string)
 * @method  setCheckboxCheckCallback(string $string)
 * @method  setRowInitCallback(string $string)
 * @method  setUseAjax(bool $true)
 */
class ProductsGrid extends Extended
{
    /**
     * Sales config
     *
     * @var SalesConfig
     */
    protected $_salesConfig;

    /**
     * Catalog config
     *
     * @var Config
     */
    protected $_catalogConfig;

    /**
     * Product factory
     *
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * ProductsGrid constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Config $catalogConfig
     * @param SalesConfig $salesConfig
     * @param Visibility $productVisibility
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Config $catalogConfig,
        SalesConfig $salesConfig,
        Visibility $productVisibility,
        array $data = []
    ) {
        $this->_productFactory    = $productFactory;
        $this->_catalogConfig     = $catalogConfig;
        $this->_salesConfig       = $salesConfig;
        $this->_productVisibility = $productVisibility;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('sales_order_create_search_grid');
        $this->setRowClickCallback('mpBetterWishlist.productGridRowClick.bind(mpBetterWishlist)');
        $this->setCheckboxCheckCallback('mpBetterWishlist.productGridCheckboxCheck.bind(mpBetterWishlist)');
        $this->setRowInitCallback('mpBetterWishlist.productGridRowInit.bind(mpBetterWishlist)');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Add column filter to collection
     *
     * @param Column $column
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Prepare collection to be displayed in the grid
     *
     * @return Extended
     * @throws NoSuchEntityException
     */
    protected function _prepareCollection()
    {
        $attributes = $this->_catalogConfig->getProductAttributes();
        /**
         * @var $collection Collection
         */
        $collection = $this->_productFactory->create()->getCollection();
        $collection->setStore(
            $this->getStore()
        )->addAttributeToSelect(
            $attributes
        )->addAttributeToSelect(
            'sku'
        )->addStoreFilter()->addAttributeToFilter(
            'type_id',
            $this->_salesConfig->getAvailableProductTypes()
        )->setVisibility($this->_productVisibility->getVisibleInSiteIds());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header'           => __('ID'),
            'sortable'         => true,
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'index'            => 'entity_id'
        ]);
        $this->addColumn('name', [
            'header'   => __('Product'),
            'renderer' => Product::class,
            'index'    => 'name'
        ]);
        $this->addColumn('sku', [
            'header' => __('SKU'),
            'index'  => 'sku'
        ]);
        $this->addColumn('price', [
            'header'           => __('Price'),
            'column_css_class' => 'price',
            'type'             => 'currency',
            'currency_code'    => $this->getStore()->getCurrentCurrencyCode(),
            'rate'             => $this->getStore()->getBaseCurrency()->getRate(
                $this->getStore()->getCurrentCurrencyCode()
            ),
            'index'            => 'price',
            'renderer'         => Price::class
        ]);

        $this->addColumn('in_products', [
            'header'           => __('Select'),
            'type'             => 'checkbox',
            'name'             => 'in_products',
            'values'           => $this->_getSelectedProducts(),
            'index'            => 'entity_id',
            'sortable'         => false,
            'header_css_class' => 'col-select',
            'column_css_class' => 'col-select'
        ]);

        $this->addColumn('qty', [
            'filter'         => false,
            'sortable'       => false,
            'header'         => __('Quantity'),
            'renderer'       => Qty::class,
            'name'           => 'qty',
            'inline_css'     => 'qty',
            'type'           => 'input',
            'validate_class' => 'validate-number',
            'index'          => 'qty'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpwishlist/customer/editWishlist', ['collapse' => null, '_current' => true]);
    }

    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        return $this->getRequest()->getPost('products', []);
    }

    /**
     * Add custom options to product collection
     *
     * @return Extended
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addOptionsToResult();

        return parent::_afterLoadCollection();
    }
}
