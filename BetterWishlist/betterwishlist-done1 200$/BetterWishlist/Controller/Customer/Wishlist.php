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
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Block\Customer\Wishlist as WishlistBlock;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ItemFactory;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Wishlist
 *
 * @package Mageplaza\BetterWishlist\Controller\Customer
 */
class Wishlist extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var WishlistBlock
     */
    protected $wishlistBlock;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MpWishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Wishlist constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param WishlistBlock $wishlistBlock
     * @param ItemFactory $itemFactory
     * @param LoggerInterface $logger
     * @param MpWishlistItemFactory $mpWishlistItemFactory
     * @param CategoryFactory $categoryFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        WishlistBlock $wishlistBlock,
        ItemFactory $itemFactory,
        LoggerInterface $logger,
        MpWishlistItemFactory $mpWishlistItemFactory,
        CategoryFactory $categoryFactory,
        Data $helperData
    ) {
        $this->resultPageFactory     = $resultPageFactory;
        $this->storeManager          = $storeManager;
        $this->customerSession       = $customerSession;
        $this->wishlistBlock         = $wishlistBlock;
        $this->itemFactory           = $itemFactory;
        $this->logger                = $logger;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
        $this->categoryFactory       = $categoryFactory;
        $this->helperData            = $helperData;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Layout
     */
    public function execute()
    {
        $customerId  = $this->customerSession->getId();
        $type        = $this->getRequest()->getParam('type');
        $productGrid = false;
        $layout      = $this->resultPageFactory->create()->addHandle('wishlist_index_index')->getLayout();

        if (!$type || !$customerId) {
            return $this->getResponse()->representJson(Data::jsonEncode(['productGrid' => $productGrid]));
        }
        if ($type && $customerId) {
            try {
                $wishlistItemId   = (int) $this->getRequest()->getParam('itemId');
                $fromCategory     = $this->getRequest()->getParam('fromCategoryId');
                $fromCategoryName = $this->getRequest()->getParam('fromCategoryName');
                $toCategory       = $this->getRequest()->getParam('toCategoryId');
                $toCategoryName   = $this->getRequest()->getParam('toCategoryName');
                $newCategory      = $this->getRequest()->getParam('newCategoryId');
                $newCategoryName  = $this->getRequest()->getParam('newCategoryName');
                $product          = new DataObject();
                if ($wishlistItemId) {
                    /**
                     * @var Item $wishlistItem
                     */
                    $wishlistItem = $this->itemFactory->create()->load($wishlistItemId);
                    $product      = $wishlistItem->getProduct();
                }

                if ($toCategory === 'new') {
                    $this->categoryFactory->create()->setData([
                        'customer_id'   => $customerId,
                        'category_id'   => $newCategory,
                        'category_name' => $newCategoryName,
                        'store_id'      => $this->storeManager->getStore()->getId(),
                    ])->save();
                    $toCategory     = $newCategory;
                    $toCategoryName = $newCategoryName;
                }
                switch ($type) {
                    case 'move':
                        try {
                            $this->moveItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
                            $productGrid = $layout->getBlock('customer.wishlist.items')
                                ->setItems($this->wishlistBlock->getWishlistItems())
                                ->setCategoryId($fromCategory)->toHtml();
                            $this->messageManager->addSuccessMessage(
                                __('You have moved %1 to %2 wishlist', $product->getName(), $toCategoryName)
                            );
                        } catch (Exception $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                        }
                        break;
                    case 'copy':
                        try {
                            $this->copyItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
                            $this->messageManager->addSuccessMessage(
                                __(
                                    'You have copied %1 from %2 wishlist to %3 wishlist',
                                    $product->getName(),
                                    $fromCategoryName,
                                    $toCategoryName
                                )
                            );
                        } catch (Exception $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                        }
                        break;
                    case 'load':
                        $productGrid = $layout->getBlock('customer.wishlist.items')
                            ->setItems($this->wishlistBlock->getWishlistItems())
                            ->setCategoryId($fromCategory)->toHtml();
                        break;
                    case 'delete':
                        try {
                            $this->deleteItem($fromCategory, $wishlistItemId);
                            $productGrid = $layout->getBlock('customer.wishlist.items')
                                ->setItems($this->wishlistBlock->getWishlistItems())
                                ->setCategoryId($fromCategory)->toHtml();
                            $this->messageManager->addSuccessMessage(
                                __('You deleted %1 from %2 wishlist', $product->getName(), $fromCategoryName)
                            );
                        } catch (Exception $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                        }
                        break;
                }
            } catch (Exception $exception) {
                $this->logger->critical($exception);
            }
        }
        $updateButton = $layout->getBlock('customer.wishlist.button.update')->toHtml();
        $shareButton  = $layout->getBlock('customer.wishlist.button.share')->toHtml();
        $toolbar      = $layout->getBlock('wishlist_item_pager');
        $toolbarHtml  = $toolbar ? $toolbar->toHtml() : '';

        $controlButtons = ['update' => $updateButton, 'share' => $shareButton];

        if ($layout->getBlock('customer.wishlist.button.toCart')) {
            $toCartButton             = $layout->getBlock('customer.wishlist.button.toCart')->toHtml();
            $controlButtons['toCart'] = $toCartButton;
        }

        return $this->getResponse()->representJson(Data::jsonEncode([
            'productGrid'    => $productGrid,
            'controlButtons' => $controlButtons,
            'toolbar'        => $toolbarHtml
        ]));
    }

    /**
     * @param $wishlistItemId
     * @param $fromCategory
     * @param $toCategory
     * @param $toCategoryName
     *
     * @throws Exception
     */
    protected function copyItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName)
    {
        $this->helperData->copyItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
    }

    /**
     * @param $wishlistItemId
     * @param $fromCategory
     * @param $toCategory
     * @param $toCategoryName
     *
     * @throws Exception
     */
    protected function moveItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName)
    {
        $this->helperData->moveItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
    }

    /**
     * @param $categoryId
     * @param $itemId
     *
     * @throws Exception
     */
    protected function deleteItem($categoryId, $itemId)
    {
        $this->helperData->deleteItem($categoryId, $itemId);
    }
}
