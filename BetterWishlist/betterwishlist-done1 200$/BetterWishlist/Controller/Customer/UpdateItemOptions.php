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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ItemFactory;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateItemOptions
 * @package Mageplaza\BetterWishlist\Controller\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateItemOptions extends AbstractIndex
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * UpdateItemOptions constructor.
     *
     * @param Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     * @param Registry $registry
     * @param ItemFactory $itemFactory
     * @param WishlistHelper $wishlistHelper
     * @param LoggerInterface $logger
     * @param WishlistItemFactory $wishlistItemFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator,
        Registry $registry,
        ItemFactory $itemFactory,
        WishlistHelper $wishlistHelper,
        LoggerInterface $logger,
        WishlistItemFactory $wishlistItemFactory,
        Data $helperData
    ) {
        $this->wishlistProvider    = $wishlistProvider;
        $this->productRepository   = $productRepository;
        $this->formKeyValidator    = $formKeyValidator;
        $this->registry            = $registry;
        $this->itemFactory         = $itemFactory;
        $this->wishlistHelper      = $wishlistHelper;
        $this->logger              = $logger;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->helperData          = $helperData;

        parent::__construct($context);
    }

    /**
     * Action to accept new configuration for a wishlist item
     * @return ResponseInterface|Redirect|ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('wishlist/index/');
        }

        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $resultRedirect->setPath('wishlist/');

            return $resultRedirect;
        }
        /**
         * @var Product $product
         */
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $resultRedirect->setPath('wishlist/');

            return $resultRedirect;
        }
        $wishlist = new DataObject();
        try {
            $itemId = (int) $this->getRequest()->getParam('id');
            /**
             * @var Item
             */
            $item = $this->itemFactory->create();
            $item->load($itemId);
            $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
            if (!$wishlist) {
                $resultRedirect->setPath('*/');

                return $resultRedirect;
            }
            $buyRequest = new DataObject($this->getRequest()->getParams());
            $updateQty  = $buyRequest->getQty();
            $categoryId = $this->getRequest()->getParam('fromCategoryId');
            if ($categoryId === 'all') {
                $this->helperData->updateAllItemQty($item, $buyRequest->getQty());
                $mpWishlistItem = $this->wishlistItemFactory->create()
                    ->loadItem($itemId, $this->helperData->getDefaultCategory()->getId());
            } else {
                /**
                 * @var WishlistItem $mpWishlistItem
                 */
                $mpWishlistItem = $this->wishlistItemFactory->create()->loadItem($itemId, $categoryId);
                if ($mpWishlistItem->getId()) {
                    $allQty    = $item->getQty();
                    $qty       = $mpWishlistItem->getQty();
                    $changeQty = $updateQty - $qty;
                    $buyRequest->setQty($allQty + $changeQty);
                }
            }

            $wishlist->updateItem($itemId, $buyRequest)->save();
            /** @var WishlistItem $item */
            $item = $this->registry->registry('mp_wishlist_item');
            if ($item && $item->getId() && (int) $item->getId() !== $itemId) {
                $collection = $this->wishlistItemFactory->create()->getCollection()
                    ->addFieldToFilter('wishlist_item_id', $itemId);
                foreach ($collection as $collectionItem) {
                    $collectionItem->setWishlistItemId($item->getId())->save();
                }
            }
            $mpWishlistItem->setQty($updateQty)->save();

            $this->wishlistHelper->calculate();
            $this->_eventManager->dispatch(
                'wishlist_update_item',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $wishlist->getItem($itemId)]
            );

            $message = __('%1 has been updated in your Wish List.', $product->getName());
            $this->messageManager->addSuccessMessage($message);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t update your Wish List right now.'));
            $this->logger->critical($e);
        }
        $resultRedirect->setPath('wishlist/index', ['wishlist_id' => $wishlist->getId()]);

        return $resultRedirect;
    }
}
