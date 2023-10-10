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

namespace Mageplaza\BetterWishlist\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;

/**
 * Class UpdateItem
 * @package Mageplaza\BetterWishlist\Observer
 */
class UpdateItem implements ObserverInterface
{
    /**
     * @var Collection
     */
    protected $wishlistItem;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var MpWishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * UpdateItem constructor.
     *
     * @param Collection $wishlistItem
     * @param Session $customerSession
     * @param CategoryFactory $categoryFactory
     * @param MpWishlistItemFactory $mpWishlistItemFactory
     */
    public function __construct(
        Collection $wishlistItem,
        Session $customerSession,
        CategoryFactory $categoryFactory,
        MpWishlistItemFactory $mpWishlistItemFactory
    ) {
        $this->wishlistItem          = $wishlistItem;
        $this->customerSession       = $customerSession;
        $this->categoryFactory       = $categoryFactory;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $wishlist               = $observer->getData('wishlist');
        $wishlistItemCollection = $this->wishlistItem->addFieldToFilter('wishlist_id', $wishlist->getId());
        $wishlistItemId         = '';
        $qty                    = '';
        foreach ($wishlistItemCollection as $item) {
            $wishlistItemId = $item->getWishlistItemId();
            $qty            = $item->getQty();
        }
        if ($wishlistItemId && $qty) {
            $categoryId     = $this->customerSession->getCategoryId();
            $customerId     = $this->customerSession->getCustomerId();
            $category       = $this->categoryFactory->create()->loadByCategoryId($categoryId, $customerId);
            $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $categoryId);

            $mpWishlistItem->addData([
                'wishlist_item_id' => $wishlistItemId,
                'category_id'      => $categoryId,
                'category_name'    => $category->getName(),
                'qty'              => $qty
            ])->save();
        }
    }
}
