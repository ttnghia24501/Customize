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

namespace Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem\Grid;

use Exception;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Wishlist\Model\ResourceModel\Item;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem\Grid
 */
class Collection extends SearchResult
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_wishlist_grid_item_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'grid_item_collection';

    /**
     * @var
     */
    protected $_selectedColumns;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param ProductMetadataInterface $productMetadata
     * @param RequestInterface $request
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ProductMetadataInterface $productMetadata,
        RequestInterface $request,
        $mainTable = 'wishlist_item',
        $resourceModel = Item::class
    ) {
        $this->productMetadata = $productMetadata;
        $this->request         = $request;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @return $this|SearchResult
     */
    protected function _initSelect()
    {
        $tableName = $this->getMainTable();
        $this->getSelect()->from(['main_table' => $tableName], $this->_getSelectedColumns());

        return $this;
    }

    /**
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        if (!$this->_selectedColumns) {
            $this->_selectedColumns = [
                'product_id'     => new Zend_Db_Expr('MAX(main_table.product_id)'),
                'product_name'   => new Zend_Db_Expr('MAX(cpev.value)'),
                'sku'            => new Zend_Db_Expr('MAX(cpe.sku)'),
                'product_type'   => new Zend_Db_Expr('MAX(cpe.type_id)'),
                'qty'            => new Zend_Db_Expr('MAX(IFNULL(csi.qty,0))'),
                'customer_count' => new Zend_Db_Expr('COUNT(DISTINCT wl.customer_id)'),
                'product_info'   => new Zend_Db_Expr('MAX(wlio.value)'),
                'last_added'     => sprintf(
                    '%s',
                    $connection->getDateFormatSql('main_table.added_at', '%Y-%m-%d %H:%i:%s')
                ),
            ];
        }

        return $this->_selectedColumns;
    }

    /**
     * @return $this
     */
    protected function _applyAggregatedTable()
    {
        $select = $this->getSelect();

        $prdId = $this->productMetadata->getEdition() === 'Enterprise' ? 'row_id' : 'entity_id';

        $select->joinLeft(
            ['cpev' => $this->getTable('catalog_product_entity_varchar')],
            "cpev.{$prdId} = main_table.product_id AND cpev.attribute_id = '73' AND cpev.store_id = '0'",
            []
        )->joinLeft(
            ['wl' => $this->getTable('wishlist')],
            'wl.wishlist_id = main_table.wishlist_id',
            []
        )->joinLeft(
            ['wli' => $this->getTable('wishlist_item')],
            'wli.wishlist_item_id = main_table.wishlist_item_id',
            []
        )->joinLeft(
            ['wlio' => $this->getTable('wishlist_item_option')],
            'wlio.wishlist_item_id = wli.wishlist_item_id',
            []
        )->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$prdId} = main_table.product_id",
            []
        )->joinLeft(
            ['ce' => $this->getTable('customer_entity')],
            'ce.entity_id = wl.customer_id',
            []
        )->joinLeft(
            ['csi' => $this->getTable('cataloginventory_stock_item')],
            'csi.product_id = main_table.product_id',
            []
        )->group('main_table.product_id');

        return $this;
    }

    /**
     * @return $this
     */
    protected function _applyDateRangeFilter()
    {
        $filter   = $this->request->getParam('mpFilter');
        $formDate = isset($filter['startDate']) ? $filter['startDate'] : null;
        $toDate   = isset($filter['endDate']) ? $filter['endDate'] : null;

        // Remember that field PERIOD is a DATE(YYYY-MM-DD) in all databases
        if ($formDate !== null) {
            $this->getSelect()->where("DATE_FORMAT(main_table.added_at,'%Y-%m-%d') >= ?", $formDate);
        }
        if ($toDate !== null) {
            $this->getSelect()->where("DATE_FORMAT(main_table.added_at,'%Y-%m-%d') <= ?", $toDate);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _applyStoreFilter()
    {
        $filter = $this->request->getParam('mpFilter');
        $store  = isset($filter['store']) ? $filter['store'] : 0;
        if ($store) {
            $this->getSelect()->where('main_table.store_id =?', $store);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _applyCustomerGroupFilter()
    {
        $customerGroup = isset($this->request->getParam('mpFilter')['customer_group_id'])
            ? (int) $this->request->getParam('mpFilter')['customer_group_id']
            : 32000;
        if ($customerGroup !== 32000) {
            $this->getSelect()->where('ce.group_id=' . $customerGroup);
        }

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this|SearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case 'qty':
                $field = 'csi.qty';
                break;
            case 'product_id':
                $field = 'main_table.product_id';
                break;
            case 'last_added':
                $field = 'main_table.added_at';
                break;
            case 'product_name':
                $field = 'cpev.value';
                break;
            case 'product_type':
                $field = 'cpe.type_id';
                break;
            case 'customer_count':
                if (isset($condition['gteq'])) {
                    $this->getSelect()->having("{$field} >= {$condition['gteq']}");
                }
                if (isset($condition['lteq'])) {
                    $this->getSelect()->having("{$field} <= {$condition['lteq']}");
                }

                return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return Select
     */
    public function getSelectCountSql()
    {
        if ($this->request->getFullActionName() === 'mui_export_gridToCsv') {
            return parent::getSelectCountSql();
        }
        $this->_renderFilters();
        $select = clone $this->getSelect();

        $select->reset(Select::ORDER);

        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    /**
     * \
     *
     * @return $this|SearchResult
     * @throws Exception
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->_applyAggregatedTable();
        $this->_applyCustomerGroupFilter();
        $this->_applyStoreFilter();
        $this->_applyDateRangeFilter();

        return $this;
    }
}
