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
use Magento\Catalog\Pricing\Render;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Image;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\AuthenticationStateInterface;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToWishlist extends Action
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var AuthenticationStateInterface
     */
    protected $authenticationState;

    /**
     * @var RedirectInterface
     */
    protected $redirector;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

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
     * AddToWishlist constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param AuthenticationStateInterface $authenticationState
     * @param Validator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     * @param WishlistHelper $wishlistHelper
     * @param MpWishlistItemFactory $mpWishlistItemFactory
     * @param CategoryFactory $categoryFactory
     * @param Data $helperData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        AuthenticationStateInterface $authenticationState,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        WishlistHelper $wishlistHelper,
        MpWishlistItemFactory $mpWishlistItemFactory,
        CategoryFactory $categoryFactory,
        Data $helperData
    ) {
        $this->_customerSession      = $customerSession;
        $this->wishlistProvider      = $wishlistProvider;
        $this->productRepository     = $productRepository;
        $this->authenticationState   = $authenticationState;
        $this->redirector            = $context->getRedirect();
        $this->formKeyValidator      = $formKeyValidator;
        $this->storeManager          = $storeManager;
        $this->resultPageFactory     = $resultPageFactory;
        $this->wishlistHelper        = $wishlistHelper;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
        $this->categoryFactory       = $categoryFactory;
        $this->helperData            = $helperData;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $session = $this->_customerSession;

        $requestParams = $this->getRequest()->getParams();
        $afterLogin    = false;
        if ($session->getBeforeWishlistRequest()) {
            $afterLogin = true;
        }
        if ($this->authenticationState->isEnabled() && !$session->isLoggedIn()) {
            if ($afterLogin) {
                $resultRedirect->setPath('customer/account/login');

                return $resultRedirect;
            }

            if (!$session->getBeforeWishlistUrl()) {
                $session->setBeforeWishlistUrl($this->redirector->getRefererUrl());
            }
            $session->setBeforeWishlistRequest($requestParams);
            $session->setBeforeRequestParams($requestParams);
            $session->setBeforeModuleName('mpwishlist');
            $session->setBeforeControllerName('customer');
            $session->setBeforeAction('addToWishlist');

            $result = [
                'error'   => true,
                'backUrl' => $this->_url->getUrl('customer/account/login')
            ];

            return $this->getResponse()->representJson($this->helperData->jsEncode($result));
        }

        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->setBeforeWishlistRequest(null);
        }
        if ($afterLogin === false && !$this->formKeyValidator->validate($this->getRequest())) {
            $result = [
                'error'   => true,
                'backUrl' => $this->_url->getUrl('wishlist/index')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            if ($afterLogin) {
                throw new NotFoundException(__('Page not found.'));
            }
            $result = [
                'error'   => true,
                'message' => __('Page not found.')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $productId = isset($requestParams['product']) ? (int) $requestParams['product'] : null;
        if (!$productId) {
            if ($afterLogin) {
                $resultRedirect->setPath('wishlist/');

                return $resultRedirect;
            }

            $result = [
                'error'   => true,
                'message' => __('We can\'t specify a product.')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
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
            if ($afterLogin) {
                $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
                $resultRedirect->setPath('wishlist/');

                return $resultRedirect;
            }
            $result = [
                'error'   => true,
                'message' => __('We can\'t specify a product.')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        try {
            $buyRequest   = new DataObject($requestParams);
            $wishlistItem = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($wishlistItem)) {
                $error  = new LocalizedException(__($wishlistItem));
                $result = ['error' => true, 'message' => $error->getMessage()];

                return $this->getResponse()->representJson(Data::jsonEncode($result));
            }
            $wishlistItemId  = $wishlistItem->getId();
            $storeId         = $this->storeManager->getStore()->getId();
            $toCategory      = isset($requestParams['toCategoryId']) ? $requestParams['toCategoryId'] : null;
            $toCategoryName  = isset($requestParams['toCategoryName']) ? $requestParams['toCategoryName'] : null;
            $newCategory     = isset($requestParams['newCategoryId']) ? $requestParams['newCategoryId'] : null;
            $newCategoryName = isset($requestParams['newCategoryName']) ? $requestParams['newCategoryName'] : null;
            $customerId      = $session->getCustomerId();
            if ($toCategory === 'new') {
                $collectionCount = $this->categoryFactory->create()->getCollection()
                    ->addFieldToFilter('customer_id', $customerId)->getSize();
                /**
                 * @var \Mageplaza\BetterWishlist\Model\Category $category
                 */
                $category = $this->categoryFactory->create()->getCollection()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('category_name', $newCategoryName)
                    ->getFirstItem();
                if ($category->getId()) {
                    $newCategory = $category->getCategoryId();
                } else {
                    if ($collectionCount >= $this->helperData->getLimitWishlist($storeId)) {
                        if ($afterLogin) {
                            $this->messageManager->addErrorMessage(__('Wishlist number limit has been reached.'));
                            $resultRedirect->setPath('wishlist/index');

                            return $resultRedirect;
                        }

                        $result = [
                            'error'   => true,
                            'message' => __('Wishlist number limit has been reached.')
                        ];

                        return $this->getResponse()->representJson(Data::jsonEncode($result));
                    }
                    $category->setData([
                        'customer_id'   => $customerId,
                        'category_id'   => $newCategory,
                        'category_name' => $newCategoryName,
                        'store_id'      => $storeId,
                    ])->save();
                }
                $toCategory     = $newCategory;
                $toCategoryName = $newCategoryName;
            }
            if ($toCategory === 'all' || !$toCategory) {
                $toCategory     = $this->helperData->getDefaultCategory()->getId();
                $toCategoryName = __('Wishlist');
            }
            $wishlist->save();
            /**
             * @var WishlistItem $mpWishlistItem
             */
            $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($wishlistItemId, $toCategory);

            $mpWishlistItem->addData([
                'wishlist_item_id' => $wishlistItemId,
                'category_id'      => $toCategory,
                'category_name'    => $toCategoryName,
                'qty'              => ($buyRequest['qty'] ?: 1) + (int) $mpWishlistItem->getQty()
            ])->save();
            $this->_eventManager->dispatch('wishlist_add_product', [
                'wishlist' => $wishlist,
                'product'  => $product,
                'item'     => $wishlistItem
            ]);

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            }

            $this->wishlistHelper->calculate();

            if ($afterLogin) {
                $resultRedirect->setPath('wishlist', [
                    'wishlist_id'    => $wishlist->getId(),
                    'fromCategoryId' => $mpWishlistItem->getCategoryId()
                ]);

                return $resultRedirect;
            }
            $item      = $wishlistItem->setCategoryName($mpWishlistItem->getCategoryName())
                ->setQty($buyRequest['qty'] ?: 0); // add 0 to wishlist
            $popupHtml = $this->getPopupHtml($item);
            $result    = [
                'error' => false,
                'popup' => $popupHtml,
            ];

            return $this->getResponse()->representJson($this->helperData->jsEncode($result));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to Wish List right now.')
            );
        }

        if ($afterLogin) {
            $resultRedirect->setPath('wishlist', ['wishlist_id' => $wishlist->getId()]);

            return $resultRedirect;
        }
        $result = [
            'error'   => false,
            'message' => __('Something went wrong while adding to wishlist')
        ];

        return $this->getResponse()->representJson(Data::jsonEncode($result));
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    protected function getPopupHtml($item)
    {
        $page       = $this->resultPageFactory->create();
        $layout     = $page->getLayout();
        $popupBlock = $layout->createBlock(\Mageplaza\BetterWishlist\Block\Customer\Wishlist\Category::class);
        $popupBlock->setTemplate('addafter.phtml');
        $imgBlock = $layout->createBlock(Image::class);
        $imgBlock->setTemplate('Magento_Wishlist::item/column/image.phtml');
        $priceBlock = $layout->createBlock(\Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart::class);
        $priceBlock->setTemplate('item/column/price.phtml');
        $priceRenderBlock = $layout->createBlock(Render::class)
            ->setData([
                'price_render'    => 'product.price.render.default',
                'price_type_code' => 'wishlist_configured_price',
                'price_label'     => false,
                'zone'            => 'item_list',
            ]);
        $priceBlock->setChild('product.price.render.mpwishlist', $priceRenderBlock);

        return $popupBlock->setChild('mpwishlist.item.image', $imgBlock)
            ->setChild('mpwishlist.item.price', $priceBlock)->setItem($item)->toHtml();
    }
}
