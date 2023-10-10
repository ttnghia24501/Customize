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

namespace Mageplaza\BetterWishlist\Controller\Customer;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ItemFactory;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory;

/**
 * Class Category
 *
 * @package Mageplaza\BetterWishlist\Controller\Customer
 */
class Category extends Action
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param WishlistItemFactory $wishlistItemFactory
     * @param CategoryFactory $categoryFactory
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        WishlistItemFactory $wishlistItemFactory,
        CategoryFactory $categoryFactory,
        ItemFactory $itemFactory
    ) {
        $this->customerSession     = $customerSession;
        $this->storeManager        = $storeManager;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->categoryFactory     = $categoryFactory;
        $this->itemFactory         = $itemFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $customerId   = $this->customerSession->getCustomerId();
        $categoryId   = $this->getRequest()->getParam('categoryId');
        $categoryName = $this->getRequest()->getParam('categoryName');
        $isDelete     = $this->getRequest()->getParam('delete');

        try {
            /**
             * @var \Mageplaza\BetterWishlist\Model\Category $category
             */
            $category = $this->categoryFactory->create()->loadByCategoryId($categoryId, $customerId);
            if ($isDelete) {
                $category->delete();
                $collection = $this->wishlistItemFactory->create()->getCollection()
                    ->addFieldToFilter('category_id', $categoryId);
                /**
                 * @var WishlistItem $item
                 */
                foreach ($collection as $item) {
                    $qty            = $item->getQty();
                    $wishlistItemId = $item->getWishlistItemId();
                    $wishlistItem   = $this->itemFactory->create()->load($wishlistItemId);
                    if ($wishlistItem->getQty() == $qty) {
                        $wishlistItem->delete();
                    } else {
                        $wishlistItem->setQty($wishlistItem->getQty() - $qty)->save();
                    }
                    $item->delete();
                }
            } else {
                $category->addData(
                    [
                        'customer_id'   => $customerId,
                        'category_id'   => $categoryId,
                        'category_name' => $categoryName,
                        'store_id'      => $this->storeManager->getStore()->getId(),
                    ]
                )->save();
            }
            $error = false;
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $error = true;
        }

        return $this->getResponse()->representJson(Data::jsonEncode(['error' => $error]));
    }
}
