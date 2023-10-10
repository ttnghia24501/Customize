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

namespace Mageplaza\BetterWishlist\Model\ResourceModel\Item\Collection;

use Magento\Catalog\Model\Entity\AttributeFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\ConfigFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Helper\Admin;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Config;
use Magento\Wishlist\Model\ResourceModel\Item;
use Magento\Wishlist\Model\ResourceModel\Item\Collection\Grid as ItemCollectionGrid;
use Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory as OptionCollectionFactory;
use Mageplaza\BetterWishlist\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Grid
 * @package Mageplaza\BetterWishlist\Model\ResourceModel\Item\Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grid extends ItemCollectionGrid
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Grid constructor.
     *
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param Admin $adminhtmlSales
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param Config $wishlistConfig
     * @param Visibility $productVisibility
     * @param ResourceConnection $coreResource
     * @param OptionCollectionFactory $optionCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ConfigFactory $catalogConfFactory
     * @param AttributeFactory $catalogAttrFactory
     * @param Item $resource
     * @param State $appState
     * @param Registry $registry
     * @param RequestInterface $request
     * @param Data $helperData
     * @param AdapterInterface|null $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StockConfigurationInterface $stockConfiguration,
        Admin $adminhtmlSales,
        StoreManagerInterface $storeManager,
        DateTime $date,
        Config $wishlistConfig,
        Visibility $productVisibility,
        ResourceConnection $coreResource,
        OptionCollectionFactory $optionCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ConfigFactory $catalogConfFactory,
        AttributeFactory $catalogAttrFactory,
        Item $resource,
        State $appState,
        Registry $registry,
        RequestInterface $request,
        Data $helperData,
        AdapterInterface $connection = null
    ) {
        $this->request    = $request;
        $this->helperData = $helperData;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $stockConfiguration,
            $adminhtmlSales,
            $storeManager,
            $date,
            $wishlistConfig,
            $productVisibility,
            $coreResource,
            $optionCollectionFactory,
            $productCollectionFactory,
            $catalogConfFactory,
            $catalogAttrFactory,
            $resource,
            $appState,
            $registry,
            $connection
        );
    }

    /**
     * @return $this|ItemCollectionGrid
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $resource   = $this->getResource();
        $categoryId = $this->getCategoryId();

        if (!$categoryId || $categoryId === 'all') {
            return $this;
        }
        $this->getSelect()
            ->joinInner(
                ['mp_wl_item' => $resource->getTable('mageplaza_wishlist_item')],
                "mp_wl_item.wishlist_item_id = main_table.wishlist_item_id AND mp_wl_item.category_id = '{$categoryId}' AND mp_wl_item.qty > 0",
                ['qty' => 'mp_wl_item.qty', 'added_at' => 'mp_wl_item.added_at', 'id' => 'mp_wl_item.id']
            );

        return $this;
    }

    /**
     * @return mixed|string
     */
    private function getCategoryId()
    {
        $categoryId = $this->request->getParam('categoryId');
        if (!$categoryId) {
            if ($this->helperData->multiWishlistIsEnabled()) {
                $categoryId = $this->helperData->getDefaultCategory()->getId();
            } else {
                $categoryId = 'all';
            }
        }

        return $categoryId;
    }

    /**
     * @param $field
     *
     * @return string
     */
    private function changeFieldName($field)
    {
        $categoryId = $this->getCategoryId();

        if ($categoryId && $categoryId !== 'all') {
            switch ($field) {
                case 'qty':
                    $field = 'mp_wl_item.qty';
                    break;
                case 'added_at':
                    $field = 'mp_wl_item.added_at';
                    break;
            }
        }

        return $field;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return AbstractDb
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $field = $this->changeFieldName($field);

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return AbstractDb
     */
    public function setOrder($field, $direction = ItemCollectionGrid::SORT_ORDER_DESC)
    {
        if ($field === 'days_in_wishlist') {
            if ($this->request->getParam('categoryId') !== 'all') {
                $field     = 'mp_wl_item.added_at';
                $direction = $direction === self::SORT_ORDER_DESC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;
            }

            return parent::setOrder($field, $direction);
        }
        $field = $this->changeFieldName($field);

        return parent::setOrder($field, $direction);
    }
}
