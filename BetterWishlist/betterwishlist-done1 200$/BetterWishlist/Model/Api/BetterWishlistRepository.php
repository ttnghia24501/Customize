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
 * @package     Mageplaza_BetterWishlist
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Model\Api;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Webapi\Exception as ApiException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Mageplaza\BetterWishlist\Api\BetterWishlistRepositoryInterface;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\Api\Data\Item;
use Mageplaza\BetterWishlist\Model\Category;
use Mageplaza\BetterWishlist\Model\CategoryFactory as MpWishlistCategoryFactory;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;
use Mageplaza\BetterWishlist\Api\Data\CategoryInterface;
use Magento\Wishlist\Model\ItemFactory as ItemModel;

/**
 * Class BetterWishlistRepository
 * @package Mageplaza\BetterWishlist\Model\Api
 */
class BetterWishlistRepository implements BetterWishlistRepositoryInterface
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var WishlistFactory
     */
    protected $wishlistModel;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var MpWishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var MpWishlistCategoryFactory
     */
    protected $mpWishlistCategoryFactory;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var ItemModel
     */
    protected $itemModel;

    /**
     * BetterWishlistRepository constructor.
     *
     * @param WishlistFactory $wishlistModel
     * @param ProductRepositoryInterface $productRepository
     * @param MpWishlistItemFactory $mpWishlistItemFactory
     * @param MpWishlistCategoryFactory $mpWishlistCategoryFactory
     * @param WishlistHelper $wishlistHelper
     * @param ManagerInterface $_eventManager
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param ItemModel $itemModel
     * @param Data $helperData
     */
    public function __construct(
        WishlistFactory $wishlistModel,
        ProductRepositoryInterface $productRepository,
        MpWishlistItemFactory $mpWishlistItemFactory,
        MpWishlistCategoryFactory $mpWishlistCategoryFactory,
        WishlistHelper $wishlistHelper,
        ManagerInterface $_eventManager,
        StoreManagerInterface $storeManager,
        DateTime $date,
        ItemModel $itemModel,
        Data $helperData
    ) {
        $this->_helperData               = $helperData;
        $this->wishlistModel             = $wishlistModel;
        $this->productRepository         = $productRepository;
        $this->mpWishlistItemFactory     = $mpWishlistItemFactory;
        $this->mpWishlistCategoryFactory = $mpWishlistCategoryFactory;
        $this->_eventManager             = $_eventManager;
        $this->wishlistHelper            = $wishlistHelper;
        $this->storeManager              = $storeManager;
        $this->date                      = $date;
        $this->itemModel                 = $itemModel;
    }

    /**
     * @inheritDoc
     */
    public function addItemToCategory($productId, $categoryId, $customerId)
    {
        $requestParams = [
            'product' => $productId
        ];

        /**
         * @var Wishlist $wishlist
         */
        $wishlist = $this->wishlistModel->create()->loadByCustomerId($customerId);
        if (!$wishlist) {
            throw new ApiException(__('Page not found.'), 101);
        }

        if (!$productId) {
            throw new ApiException(__('We can\'t specify a product.'), 101);
        }

        /**
         * @var Product $product
         */
        try {
            $product = $this->productRepository->getById($productId);
        } catch (Exception $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            throw new ApiException(__('We can\'t specify a product.'), 101);
        }

        try {
            $buyRequest     = new DataObject($requestParams);
            $wishlistItem   = $wishlist->addNewItem($product, $buyRequest);
            $wishlistItemId = $wishlistItem->getId();
            if ($categoryId) {
                $category = $this->mpWishlistCategoryFactory->create()
                    ->loadByCategoryId($categoryId, $customerId);

                if (!$category->getCategoryId()) {
                    throw new ApiException(__('Mageplaza wishlist category does not exist.'), 101);
                }

                $categoryName = $category->getCategoryName();
            } else {
                $categoryId   = $this->_helperData->getDefaultCategory()->getId();
                $categoryName = __('Wishlist');
            }
            $wishlist->save();
            /**
             * @var WishlistItem $mpWishlistItem
             */
            $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $categoryId);

            $mpWishlistItem->addData([
                'wishlist_item_id' => $wishlistItemId,
                'category_id'      => $categoryId,
                'category_name'    => $categoryName,
                'qty'              => ($buyRequest['qty'] ?: 1) + (int) $mpWishlistItem->getQty()
            ])->save();
            $this->_eventManager->dispatch('wishlist_add_product', [
                'wishlist' => $wishlist,
                'product'  => $product,
                'item'     => $wishlistItem
            ]);

            $this->wishlistHelper->calculate();

            return true;
        } catch (Exception $e) {

            throw new ApiException(__($e->getMessage()), 101);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAllCategories($customerId, $isItems)
    {
        return $this->getCategoriesByParams($customerId, '', $isItems);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryById($customerId, $categoryId, $isItems)
    {
        return $this->getCategoriesByParams($customerId, $categoryId, $isItems);
    }

    /**
     * @inheritDoc
     */
    public function createCategory(CategoryInterface $category, $customerId)
    {
        if (empty($category->getCategoryName())) {
            throw new ApiException(__('Category name is not empty.'), 101);
        }

        $wishlistCategory = $this->mpWishlistCategoryFactory->create();
        $wishlistCount    = $wishlistCategory->getCollection()
            ->addFieldToFilter('customer_id', $customerId)->getSize();
        $milliseconds     = str_split(strrev(intval(microtime(true) * 1000)), 3)[0];

        /**
         * @var Category $category
         */
        $wishlistCategory = $wishlistCategory->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('category_name', $category->getCategoryName())
            ->getFirstItem();

        if ($wishlistCategory->getId()) {
            return $wishlistCategory;
        }

        if ($wishlistCount >= $this->_helperData->getLimitWishlist($this->storeManager->getStore()->getId())) {
            throw new ApiException(__('Wishlist number limit has been reached.'), 101);
        }

        try {
            $wishlistCategory->setCategoryName($category->getCategoryName())
                ->setData('is_default', false)
                ->setCustomerId($customerId)
                ->setCategoryId($this->date->timestamp() . '_' . $milliseconds)
                ->setData('items', []);
            $wishlistCategory->save();

            return $wishlistCategory;
        } catch (Exception $exception) {
            throw new ApiException(__($exception->getMessage()), 101);
        }
    }

    /**
     * @inheritDoc
     */
    public function editCategory(CategoryInterface $category, $customerId)
    {
        if (empty($category->getCategoryName())) {
            throw new ApiException(__('Category name is not empty.'), 101);
        }

        $wishlistCategory = $this->getCategory($category->getCategoryId(), $customerId);

        try {
            $wishlistCategory->setCategoryName($category->getCategoryName())->save();

            return $wishlistCategory;
        } catch (Exception $exception) {
            throw new ApiException(__($exception->getMessage()), 101);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteCategory($categoryId, $customerId)
    {
        $category = $this->getCategory($categoryId, $customerId);

        try {
            $category->delete();
            $collection = $this->mpWishlistItemFactory->create()->getCollection()
                ->addFieldToFilter('category_id', $categoryId);
            /**
             * @var WishlistItem $item
             */
            foreach ($collection as $item) {
                $qty            = $item->getQty();
                $wishlistItemId = $item->getWishlistItemId();
                $wishlistItem   = $this->itemModel->create()->load($wishlistItemId);
                if ($wishlistItem->getQty() === $qty) {
                    $wishlistItem->delete();
                } else {
                    $wishlistItem->setQty($wishlistItem->getQty() - $qty)->save();
                }
                $item->delete();
            }

            return true;
        } catch (Exception $exception) {
            throw new ApiException(__($exception->getMessage()), 101);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function removeItemInCategory($productId, $categoryId, $customerId)
    {
        /**
         * @var Wishlist $wishlist
         */
        $wishlist = $this->wishlistModel->create()->loadByCustomerId($customerId);
        if (!$wishlist) {
            throw new ApiException(__('Page not found.'), 101);
        }

        if (!$productId) {
            throw new ApiException(__('We can\'t specify a product.'), 101);
        }

        $wishlistItem = $this->_helperData->getItemByProductId($productId, $categoryId);

        if (!$wishlistItem || !$wishlistItem->getId()) {
            throw new ApiException(__('The catalog does not contain input product'), 101);
        }

        $category = $this->getCategory($categoryId, $customerId);

        if (!$category->getCategoryId()) {
            throw new ApiException(__('Mageplaza wishlist category does not exist.'), 101);
        }

        $this->_helperData->deleteItem($categoryId, $wishlistItem->getData('wishlist_item_id'));
        $wishlist->save();

        return true;
    }

    /**
     * @param string $categoryId
     * @param int $customerId
     *
     * @return Category
     * @throws Exception
     * @throws ApiException
     */
    public function getCategory($categoryId, $customerId)
    {
        if (empty($categoryId)) {
            throw new ApiException(__('Category id is not empty.'), 101);
        }

        $category = $this->mpWishlistCategoryFactory->create()
            ->loadByCategoryId($categoryId, $customerId);

        if (!$category->getCategoryId()) {
            throw new ApiException(__('Category is not exits.'), 101);
        }

        if ($category->getIsDefault()) {
            throw new ApiException(__('The default category cannot be edited.'), 101);
        }

        return $category;
    }

    /**
     * @param int $customerId
     * @param string $categoryId
     * @param boolean $isItems
     *
     * @return array
     * @throws Exception
     * @throws ApiException
     */
    public function getCategoriesByParams($customerId, $categoryId, $isItems)
    {
        $category   = $this->mpWishlistCategoryFactory->create();
        $categories = [];
        $items      = [];

        if ($isItems) {
            $wishlist = $this->wishlistModel->create()->loadByCustomerId($customerId);

            foreach ($wishlist->getItemCollection()->getItems() as $item) {
                $mpItem                            = $this->mpWishlistItemFactory->create()->load(
                    $item->getData('wishlist_item_id'),
                    'wishlist_item_id'
                );
                $items[$mpItem->getCategoryId()][] = new Item($item->getData());
            }
        }

        if ($categoryId) {
            $category      = $category->loadByCategoryId($categoryId, $customerId);
            $categoryItems = [];

            if (!$category->getCategoryId()) {
                throw new ApiException(__('Mageplaza wishlist category does not exist.'), 101);
            }

            if ($isItems) {
                $categoryItems = isset($items[$category->getCategoryId()]) ? $items[$category->getCategoryId()] : [];
            }
            $category->setItems($categoryItems);

            return [$category];
        }

        foreach ($category->getCollection()->getItems() as $wishlistCategory) {
            $categoryItems = [];
            if ($isItems) {
                $categoryItems = isset($items[$wishlistCategory->getCategoryId()]) ?
                    $items[$wishlistCategory->getCategoryId()] : [];
            }

            $wishlistCategory->setItems($categoryItems);
            $wishlistCategory->setData('is_default', false);

            $categories[] = $wishlistCategory;
        }

        $defaultCategory = $this->_helperData->getDefaultCategoryCollection($this->storeManager->getStore()->getId());

        if (!empty($defaultCategory)) {
            foreach ($defaultCategory as $wishlistCategory) {
                $categoryItems = [];
                $category      = $this->mpWishlistCategoryFactory->create();
                $category->setData(
                    [
                        'category_id'   => $wishlistCategory['id'],
                        'customer_id'   => $customerId,
                        'is_default'    => true,
                        'category_name' => $wishlistCategory['name'],
                    ]
                );

                if ($isItems) {
                    $categoryItems = isset($items[$wishlistCategory['id']]) ?
                        $items[$wishlistCategory['id']] : [];
                }

                $category->setItems($categoryItems);
                $categories[] = $category;
            }
        }

        return $categories;
    }
}
