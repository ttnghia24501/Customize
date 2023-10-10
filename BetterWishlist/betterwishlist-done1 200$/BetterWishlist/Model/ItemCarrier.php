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

namespace Mageplaza\BetterWishlist\Model;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\Wishlist;
use Mageplaza\BetterWishlist\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class ItemCarrier
 * @package Mageplaza\BetterWishlist\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemCarrier
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var WishlistHelper
     */
    protected $helper;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirector;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * ItemCarrier constructor.
     *
     * @param Session $customerSession
     * @param LocaleQuantityProcessor $quantityProcessor
     * @param Cart $cart
     * @param Logger $logger
     * @param WishlistHelper $helper
     * @param CartHelper $cartHelper
     * @param UrlInterface $urlBuilder
     * @param MessageManager $messageManager
     * @param RedirectInterface $redirector
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param ObjectManagerInterface $objectManager
     * @param Data $helperData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Session $customerSession,
        LocaleQuantityProcessor $quantityProcessor,
        Cart $cart,
        Logger $logger,
        WishlistHelper $helper,
        CartHelper $cartHelper,
        UrlInterface $urlBuilder,
        MessageManager $messageManager,
        RedirectInterface $redirector,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        ObjectManagerInterface $objectManager,
        Data $helperData
    ) {
        $this->customerSession   = $customerSession;
        $this->quantityProcessor = $quantityProcessor;
        $this->cart              = $cart;
        $this->logger            = $logger;
        $this->helper            = $helper;
        $this->cartHelper        = $cartHelper;
        $this->urlBuilder        = $urlBuilder;
        $this->messageManager    = $messageManager;
        $this->redirector        = $redirector;
        $this->storeManager      = $storeManager;
        $this->request           = $request;
        $this->_objectManager    = $objectManager;
        $this->helperData        = $helperData;
    }

    /**
     * Move all wishlist item to cart
     *
     * @param Wishlist $wishlist
     * @param array $qtys
     *
     * @return                                        string
     * @throws                                        LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function moveAllToCart(Wishlist $wishlist, $qtys)
    {
        $isOwner       = $wishlist->isOwner($this->customerSession->getCustomerId());
        $messages      = [];
        $addedProducts = [];
        $notSalable    = [];

        $categoryId = $this->request->getParam('fromCategoryId');

        $cart       = $this->cart;
        $collection = $wishlist->getItemCollection()->setVisibilityFilter();
        foreach ($collection as $item) {
            /**
             * @var $item Item
             */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();

                // Set qty
                if (isset($qtys[$item->getId()])) {
                    $qty = $this->quantityProcessor->process($qtys[$item->getId()]);
                    if ($qty) {
                        $item->setQty($qty);
                    }
                }
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                // Add to cart
                if ($item->addToCart($cart)) {
                    $addedProducts[] = $item->getProduct();
                    if ($this->helperData->isRemoveAfterAddToCart($this->storeManager->getStore()->getId())) {
                        $this->deleteItem($categoryId, $item->getId());
                    }
                }
            } catch (LocalizedException $e) {
                if ($e instanceof ProductException) {
                    $notSalable[] = $item;
                } else {
                    $messages[] = __('%1 for "%2".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }

                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                if ($cartItem) {
                    $cart->getQuote()->deleteItem($cartItem);
                }
            } catch (Exception $e) {
                $this->logger->critical($e);
                $messages[] = __('We can\'t add this item to your shopping cart right now.');
            }
        }

        if ($isOwner) {
            $indexUrl = $this->helper->getListUrl($wishlist->getId());
        } else {
            $indexUrl = $this->urlBuilder->getUrl('wishlist/shared', ['code' => $wishlist->getSharingCode()]);
        }
        if ($this->cartHelper->getShouldRedirectToCart()) {
            $redirectUrl = $this->cartHelper->getCartUrl();
        } elseif ($this->redirector->getRefererUrl()) {
            $redirectUrl = $this->redirector->getRefererUrl();
        } else {
            $redirectUrl = $indexUrl;
        }

        if ($notSalable) {
            $products = [];
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = __(
                'We couldn\'t add the following product(s) to the shopping cart: %1.',
                join(', ', $products)
            );
        }

        if ($messages) {
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
            $redirectUrl = $indexUrl;
        }

        if ($addedProducts) {
            // save wishlist model for setting date of last update
            try {
                $wishlist->save();
            } catch (Exception $e) {
                $this->messageManager->addError(__('We can\'t update the Wish List right now.'));
                $redirectUrl = $indexUrl;
            }

            $products = [];
            foreach ($addedProducts as $product) {
                /**
                 * @var $product Product
                 */
                $products[] = '"' . $product->getName() . '"';
            }

            $this->messageManager->addSuccess(
                __('%1 product(s) have been added to shopping cart: %2.', count($addedProducts), join(', ', $products))
            );

            // save cart and collect totals
            $cart->save()->getQuote()->collectTotals();
        }
        $this->helper->calculate();

        return $redirectUrl;
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
