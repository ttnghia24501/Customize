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
use Magento\Catalog\Helper\Product\Composite;
use Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Layout;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory;

/**
 * Class Configure
 *
 * @package Mageplaza\BetterWishlist\Controller\Adminhtml\Customer
 */
class Configure extends Wishlist
{
    protected $composite;

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * Configure constructor.
     *
     * @param Context $context
     * @param Composite $composite
     * @param WishlistItemFactory $wishlistItemFactory
     */
    public function __construct(
        Context $context,
        Composite $composite,
        WishlistItemFactory $wishlistItemFactory
    ) {
        $this->composite           = $composite;
        $this->wishlistItemFactory = $wishlistItemFactory;

        parent::__construct($context);
    }

    /**
     * Ajax handler to response configuration fieldset of composite product in customer's wishlist.
     *
     * @return Layout
     */
    public function execute()
    {
        $configureResult = new DataObject();
        try {
            $this->_initData();

            $configureResult->setProductId($this->_wishlistItem->getProductId());
            $categoryId = $this->getRequest()->getParam('categoryId');
            $buyRequest = $this->_wishlistItem->getBuyRequest();
            /**
             * @var WishlistItem $mpWishlistItem
             */
            $mpWishlistItem = $this->wishlistItemFactory->create()
                ->loadItem($this->_wishlistItem->getId(), $categoryId);

            if ($mpWishlistItem->getId()) {
                $qty = $mpWishlistItem->getQty();
                $buyRequest->setQty($qty);
            }
            $configureResult->setBuyRequest($buyRequest);
            $configureResult->setCurrentStoreId($this->_wishlistItem->getStoreId());
            $configureResult->setCurrentCustomerId($this->_wishlist->getCustomerId());

            $configureResult->setOk(true);
        } catch (Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        return $this->composite->renderConfigureResult($configureResult);
    }
}
