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
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Block\Customer\Wishlist as WishlistBlock;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistData;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\ResourceModel\Item\Option\Collection;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cart extends AbstractIndex
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var CartModel
     */
    protected $cart;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var Product
     */
    protected $productHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var WishlistData
     */
    protected $helper;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var WishlistBlock
     */
    protected $wishlistBlock;

    /**
     * @var MpWishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Cart constructor.
     *
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param LocaleQuantityProcessor $quantityProcessor
     * @param ItemFactory $itemFactory
     * @param CartModel $cart
     * @param OptionFactory $optionFactory
     * @param Product $productHelper
     * @param Escaper $escaper
     * @param WishlistData $helper
     * @param CartHelper $cartHelper
     * @param Validator $formKeyValidator
     * @param StoreManagerInterface $storeManager
     * @param PageFactory $resultPageFactory
     * @param WishlistBlock $wishlistBlock
     * @param MpWishlistItemFactory $mpWishlistItemFactory
     * @param Session $customerSession
     * @param Data $helperData
     */
    public function __construct(
        Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        LocaleQuantityProcessor $quantityProcessor,
        ItemFactory $itemFactory,
        CartModel $cart,
        OptionFactory $optionFactory,
        Product $productHelper,
        Escaper $escaper,
        WishlistData $helper,
        CartHelper $cartHelper,
        Validator $formKeyValidator,
        StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory,
        WishlistBlock $wishlistBlock,
        MpWishlistItemFactory $mpWishlistItemFactory,
        Session $customerSession,
        Data $helperData
    ) {
        $this->wishlistProvider      = $wishlistProvider;
        $this->quantityProcessor     = $quantityProcessor;
        $this->itemFactory           = $itemFactory;
        $this->cart                  = $cart;
        $this->optionFactory         = $optionFactory;
        $this->productHelper         = $productHelper;
        $this->escaper               = $escaper;
        $this->helper                = $helper;
        $this->cartHelper            = $cartHelper;
        $this->formKeyValidator      = $formKeyValidator;
        $this->storeManager          = $storeManager;
        $this->resultPageFactory     = $resultPageFactory;
        $this->wishlistBlock         = $wishlistBlock;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
        $this->helperData            = $helperData;
        $this->customerSession       = $customerSession;

        parent::__construct($context);
    }

    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     *
     * @return                                        ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
        $itemId = (int) $this->getRequest()->getParam('item');
        /**
         * @var $item Item
         */
        $item = $this->itemFactory->create()->load($itemId);
        if (!$item->getId()) {
            $resultRedirect->setPath('wishlist/index');

            return $resultRedirect;
        }
        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            $resultRedirect->setPath('wishlist/index');

            return $resultRedirect;
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        if (is_array($qty)) {
            $qty = isset($qty[$itemId]) ? $qty[$itemId] : 1;
        }
        $qty = $this->quantityProcessor->process($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        $redirectUrl  = $this->_url->getUrl('wishlist/index');
        $categoryId   = $this->getRequest()->getParam('fromCategoryId') ?: 'all';
        $configureUrl = $this->_url->getUrl('wishlist/index/configure/', [
            'id'         => $item->getId(),
            'product_id' => $item->getProductId(),
            'categoryId' => $categoryId,
        ]);

        try {
            $this->customerSession->setCategoryId($categoryId);

            /**
             * @var Collection $options
             */
            $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = $this->productHelper->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                ['current_config' => $item->getBuyRequest()]
            );

            $item->mergeBuyRequest($buyRequest);

            $item->addToCart($this->cart);
            if ($this->helperData->isRemoveAfterAddToCart($this->storeManager->getStore()->getId())) {
                $this->deleteItem($categoryId, $item->getId());
            }

            $this->cart->save()->getQuote()->collectTotals();
            $wishlist->save();

            if (!$this->cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );
                $this->messageManager->addSuccessMessage($message);
            }

            if ($this->cartHelper->getShouldRedirectToCart()) {
                $redirectUrl = $this->cartHelper->getCartUrl();
            } else {
                $refererUrl = $this->_redirect->getRefererUrl();

                if ($refererUrl && $refererUrl != $configureUrl) {
                    if ($this->getRequest()->isAjax()) {
                        $resultJson   = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $page         = $this->resultPageFactory->create();
                        $layout       = $page->addHandle('wishlist_index_index')->getLayout();
                        $productGrid  = $layout->getBlock('customer.wishlist.items')
                            ->setItems($this->wishlistBlock->getWishlistItems())
                            ->setCategoryId($categoryId)->toHtml();
                        $updateButton = $layout->getBlock('customer.wishlist.button.update')->toHtml();
                        $shareButton  = $layout->getBlock('customer.wishlist.button.share')->toHtml();
                        $toCartButton = $layout->getBlock('customer.wishlist.button.toCart')->toHtml();

                        $controlButtons = [
                            'update' => $updateButton,
                            'share'  => $shareButton,
                            'toCart' => $toCartButton
                        ];

                        $resultJson->setData(['productGrid' => $productGrid, 'controlButtons' => $controlButtons]);

                        return $resultJson;
                    }
                    $redirectUrl = $refererUrl;
                }
            }
        } catch (ProductException $e) {
            $this->messageManager->addErrorMessage(__('This product(s) is out of stock.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
            $redirectUrl = $configureUrl;
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t add the item to the cart right now.'));
        }

        $this->helper->calculate();

        if ($this->getRequest()->isAjax()) {
            /**
             * @var Json $resultJson
             */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['backUrl' => $redirectUrl]);

            return $resultJson;
        }

        $resultRedirect->setUrl($redirectUrl);

        return $resultRedirect;
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
