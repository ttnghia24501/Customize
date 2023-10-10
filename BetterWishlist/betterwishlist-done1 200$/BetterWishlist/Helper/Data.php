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

namespace Mageplaza\BetterWishlist\Helper;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ItemFactory;
use Mageplaza\BetterWishlist\Block\Escaper;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\ResourceModel\Category;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\BetterWishlist\Helper
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mageplaza_better_wishlist';

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var WishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param ItemFactory $itemFactory
     * @param CategoryFactory $categoryFactory
     * @param WishlistItemFactory $mpWishlistItemFactory
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        ItemFactory $itemFactory,
        CategoryFactory $categoryFactory,
        WishlistItemFactory $mpWishlistItemFactory,
        Escaper $escaper
    ) {
        $this->customerSession       = $customerSession;
        $this->itemFactory           = $itemFactory;
        $this->categoryFactory       = $categoryFactory;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
        $this->escaper               = $escaper;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $valueToEncode
     *
     * @return string
     */
    public function jsEncode($valueToEncode)
    {
        return self::jsonEncode($valueToEncode);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isRemoveAfterAddToCart($storeId = null)
    {
        return $this->getConfigGeneral('remove_after_add_to_cart', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function multiWishlistIsEnabled($storeId = null)
    {
        return $this->getConfigGeneral('enabled_multi_wishlist', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function showAllItem($storeId = null)
    {
        return $this->getConfigGeneral('show_all_item', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDefaultWishlist($storeId = null)
    {
        return $this->getConfigGeneral('default_wishlist', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isAllowCustomerCreateWishlist($storeId = null)
    {
        return $this->getConfigGeneral('allow_customer_create_wishlist', $storeId)
            && $this->multiWishlistIsEnabled($storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getLimitWishlist($storeId = null)
    {
        return (int) ($this->getConfigGeneral('limit_number_of_wishlist', $storeId) ?: 0);
    }

    /**
     * @param null $storeId
     *
     * @return int
     */
    public function isFontAwesomeEnabled($storeId = null)
    {
        return (bool) $this->getConfigGeneral('font_awesome', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigSocial($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/social' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnableSocial($storeId = null)
    {
        return $this->getConfigSocial('enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isCompatibleWithSocialShare($storeId = null)
    {
        return $this->getConfigSocial('compatible_social_share', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getShareProviders($storeId = null)
    {
        return $this->getConfigSocial('share_providers', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return DataObject
     */
    public function getDefaultCategory($storeId = 0)
    {
        $result = new DataObject();
        try {
            $defaultConfig = $this->getDefaultWishlist($storeId);
            $collection    = $defaultConfig ? $this::jsonDecode($defaultConfig) : [];
            if (!empty($collection)) {
                $value = $collection['option']['value'];
                !empty($collection['default'][0])
                    ? $categoryId = $collection['default'][0]
                    : $categoryId = key($collection['option']['value']);

                $result->addData(
                    [
                        'id'   => $categoryId,
                        'name' =>
                            (isset($value[$categoryId][$storeId]) && trim($value[$categoryId][$storeId]) !== '')
                                ? $value[$categoryId][$storeId]
                                : $value[$categoryId][0]
                    ]
                );
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);
        }

        return $result;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getDefaultCategoryCollection($storeId = 0)
    {
        try {
            $defaultConfig = $this->getDefaultWishlist($storeId);
            $collection    = $defaultConfig ? $this::jsonDecode($defaultConfig) : [];

            $result = [];
            if (!isset($collection['default'][0])) {
                $collection['default'][0] = key($collection['option']['value']);
            }
            foreach ($collection['option']['value'] as $key => $item) {
                $result[$key] = new DataObject(
                    [
                        'id'      => $key,
                        'name'    => (isset($item[$storeId]) && trim($item[$storeId]) !== '')
                            ? $item[$storeId]
                            : $item[0],
                        'default' => $key === $collection['default'][0]
                    ]
                );
            }
        } catch (Exception $e) {
            $result = [];
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getDefaultCategoryIds()
    {
        $defaultConfig = $this->getDefaultWishlist();
        $collection    = $defaultConfig ? $this::jsonDecode($defaultConfig) : [];
        $values        = isset($collection['option']['value']) ? $collection['option']['value'] : [];

        return array_keys($values);
    }

    /**
     * @return mixed
     */
    public function getCategoryCollection()
    {
        $customerId = $this->customerSession->getId();
        $collection = $this->categoryFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId);

        return $collection;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getAllCategoryIds()
    {
        $defaultCategoryIds = $this->getDefaultCategoryIds();
        /**
         * @var Category $categoryResource
         */
        $categoryResource = $this->categoryFactory->create()->getResource();
        $categoryIds      = $categoryResource->getCategoryIds();

        return array_merge($defaultCategoryIds, $categoryIds);
    }

    /**
     * @param $wishlistItemId
     * @param $fromCategory
     * @param $toCategory
     * @param $toCategoryName
     *
     * @throws Exception
     */
    public function copyItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName)
    {
        /**
         * @var WishlistItem $mpWishlistItem
         */
        $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $fromCategory);
        /**
         * @var WishlistItem $mpToWishlistItem
         */
        $mpToWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $toCategory);
        $mpToWishlistItem->addData(
            [
                'wishlist_item_id' => $wishlistItemId,
                'category_id'      => $toCategory,
                'category_name'    => $toCategoryName,
                'qty'              => $mpToWishlistItem->getQty() + (int) $mpWishlistItem->getQty()
            ]
        )->save();

        /** @var Item $wishlistItem */
        $wishlistItem = $this->itemFactory->create()->load($wishlistItemId);

        $wishlistItem->setQty((int) $wishlistItem->getQty() + (int) $mpWishlistItem->getQty())->save();
    }

    /**
     * @param $wishlistItemId
     * @param $fromCategory
     * @param $toCategory
     * @param $toCategoryName
     *
     * @throws Exception
     */
    public function moveItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName)
    {
        /**
         * @var WishlistItem $mpWishlistItem
         */
        $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $fromCategory);
        /**
         * @var WishlistItem $mpToWishlistItem
         */
        $mpToWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $toCategory);

        $mpToWishlistItem->addData(
            [
                'wishlist_item_id' => $wishlistItemId,
                'category_id'      => $toCategory,
                'category_name'    => $toCategoryName,
                'qty'              => $mpToWishlistItem->getQty() + (int) $mpWishlistItem->getQty()
            ]
        )->save();
        $mpWishlistItem->delete();
    }

    /**
     * @param $categoryId
     * @param $itemId
     *
     * @throws Exception
     */
    public function deleteItem($categoryId, $itemId)
    {
        /**
         * @var Item $wishlistItem
         */
        $wishlistItem = $this->itemFactory->create()->load($itemId);
        if (!$categoryId || $categoryId === 'all') {
            $collection = $this->mpWishlistItemFactory->create()->getCollection()
                ->addFieldToFilter('wishlist_item_id', $itemId);
            $collection->walk('delete');
            $wishlistItem->delete();
        } else {
            /**
             * @var WishlistItem $mpWishlistItem
             */
            $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($itemId, $categoryId);
            $allQty         = $wishlistItem->getQty();
            $qty            = $mpWishlistItem->getQty();
            if ($allQty > $qty) {
                $wishlistItem->setQty($allQty - $qty)->save();
            } else {
                $wishlistItem->delete();
            }
            $mpWishlistItem->delete();
        }
    }

    /**
     * @param $productId
     * @param $categoryId
     *
     * @return DataObject|null
     */
    public function getItemByProductId($productId, $categoryId)
    {
        $collection = $this->mpWishlistItemFactory->create()->getCollection();

        if ($collection) {
            $collection->getSelect()->joinInner(
                ['wl' => $collection->getTable('wishlist_item')],
                'wl.	wishlist_item_id = main_table.wishlist_item_id',
                ['wl.product_id']
            );
            $collection->addFilterToMap('product_id', 'wl.product_id');

            $collection->addFieldToFilter('product_id', $productId)->addFieldToFilter('category_id', $categoryId);

            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @param $item
     * @param $qty
     *
     * @throws Exception
     */
    public function updateAllItemQty($item, $qty)
    {
        $defaultCategory     = $this->getDefaultCategory();
        $defaultCategoryId   = $defaultCategory->getId();
        $defaultCategoryName = $defaultCategory->getName();
        $defaultWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($item->getId(), $defaultCategoryId);

        $updateQty = $qty !== null ? $qty : 1;
        $oldQty    = $item->getQty();
        $changeQty = $updateQty - $oldQty;
        if ($changeQty > 0) {
            $defaultWishlistItem->addData([
                'wishlist_item_id' => $item->getId(),
                'category_id'      => $defaultCategoryId,
                'category_name'    => $defaultCategoryName,
                'qty'              => $defaultWishlistItem->getQty() + $changeQty
            ])->save();
        }
        if ($changeQty < 0) {
            if ((int) $defaultWishlistItem->getQty() === abs($changeQty)) {
                $defaultWishlistItem->delete();
            } elseif ($defaultWishlistItem->getQty() > abs($changeQty)) {
                $defaultWishlistItem->setQty($defaultWishlistItem->getQty() + $changeQty)->save();
            } else {
                if ($defaultWishlistItem->getId()) {
                    $changeQty = $defaultWishlistItem->getQty() + $changeQty;
                    $defaultWishlistItem->delete();
                }
                $mpItemCollection = $this->mpWishlistItemFactory->create()->getCollection()
                    ->addFieldToFilter('wishlist_item_id', $item->getId());
                /**
                 * @var WishlistItem $mpItem
                 */
                foreach ($mpItemCollection as $mpItem) {
                    if ($mpItem->getQty() > abs($changeQty)) {
                        $mpItem->setQty($mpItem->getQty() + $changeQty)->save();
                        break;
                    }

                    if ((int) $mpItem->getQty() === abs($changeQty)) {
                        $mpItem->delete();
                        break;
                    }

                    $changeQty += $mpItem->getQty();
                    $mpItem->delete();
                }
            }
        }
    }

    /**
     * @param $string
     * @param bool $escapeSingleQuote
     *
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        if ($escapeSingleQuote) {
            return $this->escaper->escapeHtmlAttr((string) $string);
        }

        return htmlspecialchars((string) $string, ENT_COMPAT, 'UTF-8', false);
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }
}
