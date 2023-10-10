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

namespace Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem as WishlistItemResource;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem
 */
class Collection extends AbstractCollection
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
    protected $_eventPrefix = 'mageplaza_wishlist_item_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'item_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(WishlistItem::class, WishlistItemResource::class);
    }

    /**
     * @return mixed
     */
    public function getTotalQty()
    {
        $clone = clone $this;
        $clone->getSelect()->columns(['total_qty' => new Zend_Db_Expr('SUM(qty)')])->group('wishlist_item_id');
        /** @var WishlistItem $item */
        $item = $clone->getFirstItem();

        return $item->getTotalQty();
    }
}
