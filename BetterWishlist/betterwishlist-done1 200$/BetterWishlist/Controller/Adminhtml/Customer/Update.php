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

namespace Mageplaza\BetterWishlist\Controller\Adminhtml\Customer;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory;

/**
 * Class Update
 * @package Mageplaza\BetterWishlist\Controller\Adminhtml\Customer
 */
class Update extends \Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist
{
    /**
     * @var WishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Update constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param WishlistItemFactory $mpWishlistItemFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        WishlistItemFactory $mpWishlistItemFactory,
        Data $helperData
    ) {
        $this->registry              = $registry;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
        $this->helperData            = $helperData;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        // Update wishlist item
        $updateResult = new DataObject();
        try {
            $this->_initData();
            $buyRequest     = new DataObject($this->getRequest()->getParams());
            $categoryId     = $this->getRequest()->getParam('categoryId');
            $wishlistItemId = (int) $this->_wishlistItem->getId();
            if ($categoryId === 'all') {
                $this->helperData->updateAllItemQty($this->_wishlistItem, $buyRequest->getQty());
            } else {
                /**
                 * @var WishlistItem $mpWishlistItem
                 */
                $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $categoryId);

                if ($mpWishlistItem->getId()) {
                    $allQty    = $this->_wishlistItem->getQty();
                    $qty       = $mpWishlistItem->getQty();
                    $updateQty = $buyRequest->getQty() !== null ? $buyRequest->getQty() : 1;
                    $changeQty = $updateQty - $qty;
                    $mpWishlistItem->setQty($updateQty)->save();
                    $buyRequest->setQty($allQty + $changeQty);
                }
            }

            $this->_wishlist->updateItem($wishlistItemId, $buyRequest)->save();
            $item = $this->registry->registry('mp_wishlist_item');

            if ($item && $item->getId() && $wishlistItemId !== (int) $item->getId()) {
                $exitsItem = $this->mpWishlistItemFactory->create()->loadItem($item->getId(), $categoryId);
                if ($exitsItem->getId()) {
                    $exitsItem->setQty($exitsItem->getQty() + $mpWishlistItem->getQty())->save();
                    $mpWishlistItem->delete();
                } else {
                    $mpWishlistItem->setWishlistItemId($item->getId())->save();
                }
                $collection = $this->mpWishlistItemFactory->create()->getCollection()
                    ->addFieldToFilter('wishlist_item_id', $wishlistItemId);
                foreach ($collection as $mpItem) {
                    $mpItem->setWishlistItemId($item->getId())->save();
                }
            }
            $updateResult->setOk(true);
        } catch (Exception $e) {
            $updateResult->setError(true);
            $updateResult->setMessage($e->getMessage());
        }
        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_session->setCompositeProductResult($updateResult);

        return $this->resultRedirectFactory->create()->setPath('catalog/product/showUpdateResult');
    }
}
