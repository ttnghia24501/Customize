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

namespace Mageplaza\BetterWishlist\Plugin\Wishlist\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Magento\Wishlist\Model\Wishlist as WishlistModel;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem as WishlistItemResource;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory;

/**
 * Class Wishlist
 * @package Mageplaza\BetterWishlist\Plugin\Wishlist\Model
 */
class Wishlist
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var WishlistItemResource
     */
    protected $wishlistItemResource;

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * Wishlist constructor.
     *
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Data $helperData
     * @param WishlistItemResource $wishlistItemResource
     * @param WishlistItemFactory $wishlistItemFactory
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        Data $helperData,
        WishlistItemResource $wishlistItemResource,
        WishlistItemFactory $wishlistItemFactory
    ) {
        $this->request              = $request;
        $this->storeManager         = $storeManager;
        $this->helperData           = $helperData;
        $this->wishlistItemResource = $wishlistItemResource;
        $this->wishlistItemFactory  = $wishlistItemFactory;
    }

    /**
     * @param WishlistModel $wishlist
     * @param Collection $result
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetItemCollection(WishlistModel $wishlist, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        $categoryId = $this->request->getParam('fromCategoryId');
        if (!$categoryId) {
            if ($this->helperData->multiWishlistIsEnabled()) {
                $categoryId = $this->helperData->getDefaultCategory($this->storeManager->getStore()->getId())->getId();
            } else {
                $categoryId = 'all';
            }
        } else {
            if (!$this->helperData->multiWishlistIsEnabled()) {
                $categoryId = 'all';
            }
        }

        if (strpos($result->getSelect()->__toString(), $this->wishlistItemResource->getMainTable()) !== false) {
            return $result;
        }

        if ($categoryId && $categoryId !== 'all'
            && $this->request->getFullActionName() !== 'mpwishlist_customer_addtowishlist') {
            $result->getSelect()->joinLeft(
                ['mp_wl_item' => $this->wishlistItemResource->getMainTable()],
                'main_table.wishlist_item_id = mp_wl_item.wishlist_item_id',
                ['qty' => 'mp_wl_item.qty', 'mp_qty' => 'mp_wl_item.qty']
            )->where('mp_wl_item.category_id = ?', $categoryId);
        }

        return $result;
    }

    /**
     * @param WishlistModel $wishlist
     * @param callable $process
     * @param $product
     * @param null $buyRequest
     * @param false $forciblySetQty
     *
     * @throws LocalizedException
     */
    public function aroundAddNewItem(WishlistModel $wishlist, callable $process, $product, $buyRequest = null, $forciblySetQty = false)
    {
        $result = $process($product, $buyRequest, $forciblySetQty);

        if ($this->request->getActionName() === 'fromcart' && $this->helperData->isEnabled()) {
            $toCategory     = $this->helperData->getDefaultCategory()->getId();
            $toCategoryName = $this->helperData->getDefaultCategory()->getName();
            $wishlistItemId = $result->getWishlistItemId();

            $mpWishlistItem = $this->wishlistItemFactory->create()->loadItem($wishlistItemId, $toCategory);

            $mpWishlistItem->addData([
                'wishlist_item_id' => $result->getWishlistItemId(),
                'category_id'      => $toCategory,
                'category_name'    => $toCategoryName,
                'qty'              => ($buyRequest['qty'] ?: 1) + (int) $mpWishlistItem->getQty()
            ])->save();
        }

        return $result;
    }
}
