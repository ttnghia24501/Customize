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

namespace Mageplaza\BetterWishlist\Block\Share\Email;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem as WishlistItemResource;

/**
 * Class Items
 *
 * @package Mageplaza\BetterWishlist\Block\Share\Email
 */
class Items extends \Magento\Wishlist\Block\Share\Email\Items
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Wishlist::email/items.phtml';

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
     * Items constructor.
     *
     * @param Context $context
     * @param HttpContext $httpContext
     * @param WishlistItemResource $wishlistItemResource
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        HttpContext $httpContext,
        WishlistItemResource $wishlistItemResource,
        Data $helperData,
        array $data = []
    ) {
        $this->request              = $context->getRequest();
        $this->storeManager         = $context->getStoreManager();
        $this->helperData           = $helperData;
        $this->wishlistItemResource = $wishlistItemResource;

        parent::__construct($context, $httpContext, $data);
    }

    /**
     * @return Collection|mixed
     * @throws LocalizedException
     */
    public function getWishlistItems()
    {
        $collection = parent::getWishlistItems();
        $collection = $this->addCategoryFilter($collection);

        return $collection;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getWishlistItemsCount()
    {
        $collection = $this->_getWishlist()->getItemCollection();
        $collection = $this->addCategoryFilter($collection);

        return (int) $collection->getSize();
    }

    /**
     * @param Collection|mixed $collection
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function addCategoryFilter($collection)
    {
        $categoryId = $this->request->getParam('categoryId');
        if (!$categoryId) {
            $categoryId = $this->helperData->getDefaultCategory($this->storeManager->getStore()->getId())->getId();
        }
        if ($categoryId && $categoryId !== 'all') {
            $select = $collection->getSelect()->__toString();
            if (strpos($select, 'mp_wl_item') !== false) {
                return $collection;
            }
            $collection->getSelect()->joinLeft(
                ['mp_wl_item' => $this->wishlistItemResource->getMainTable()],
                'main_table.wishlist_item_id = mp_wl_item.wishlist_item_id',
                ['qty' => 'mp_wl_item.qty']
            )->where('mp_wl_item.category_id = ?', $categoryId);
        }

        return $collection;
    }
}
