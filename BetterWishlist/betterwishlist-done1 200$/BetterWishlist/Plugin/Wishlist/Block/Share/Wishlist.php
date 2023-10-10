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

namespace Mageplaza\BetterWishlist\Plugin\Wishlist\Block\Share;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem as WishlistItemResource;

/**
 * Class Wishlist
 * @package Mageplaza\BetterWishlist\Plugin\Wishlist\Block\Customer
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
     * Wishlist constructor.
     *
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Data $helperData
     * @param WishlistItemResource $wishlistItemResource
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        Data $helperData,
        WishlistItemResource $wishlistItemResource
    ) {
        $this->request              = $request;
        $this->storeManager         = $storeManager;
        $this->helperData           = $helperData;
        $this->wishlistItemResource = $wishlistItemResource;
    }

    /**
     * @param \Magento\Wishlist\Block\Share\Wishlist $wishlist
     * @param Collection $result
     *
     * @return                                        mixed
     * @throws                                        LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetWishlistItems(\Magento\Wishlist\Block\Share\Wishlist $wishlist, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        $categoryId = $this->request->getParam('categoryId');

        if ($categoryId && $categoryId !== 'all') {
            $select = $result->getSelect()->__toString();
            if (strpos($select, 'mp_wl_item') !== false) {
                return $result;
            }
            $result->getSelect()->joinLeft(
                ['mp_wl_item' => $this->wishlistItemResource->getMainTable()],
                'main_table.wishlist_item_id = mp_wl_item.wishlist_item_id',
                ['qty' => 'mp_wl_item.qty']
            )->where('mp_wl_item.category_id = ?', $categoryId);
        }
        $ids = $result->getAllIds();
        foreach ($result as $item) {
            if (!in_array($item->getId(), $ids, true)) {
                $result->removeItemByKey($item->getId());
            }
        }

        return $result;
    }
}
