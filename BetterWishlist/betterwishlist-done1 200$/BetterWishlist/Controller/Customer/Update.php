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
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Wishlist\Block\Customer\Sharing;
use Magento\Wishlist\Block\Customer\Wishlist as WishlistBlock;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Update
 *
 * @package Mageplaza\BetterWishlist\Controller\Customer
 */
class Update extends AbstractIndex
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var WishlistBlock
     */
    protected $wishlistBlock;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var MpWishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Action\Context $context
     * @param Validator $formKeyValidator
     * @param WishlistProviderInterface $wishlistProvider
     * @param LocaleQuantityProcessor $quantityProcessor
     * @param PageFactory $resultPageFactory
     * @param WishlistBlock $wishlistBlock
     * @param WishlistHelper $wishlistHelper
     * @param ItemFactory $itemFactory
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     * @param MpWishlistItemFactory $mpWishlistItemFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        WishlistProviderInterface $wishlistProvider,
        LocaleQuantityProcessor $quantityProcessor,
        PageFactory $resultPageFactory,
        WishlistBlock $wishlistBlock,
        WishlistHelper $wishlistHelper,
        ItemFactory $itemFactory,
        LoggerInterface $logger,
        Escaper $escaper,
        MpWishlistItemFactory $mpWishlistItemFactory,
        Data $helperData
    ) {
        $this->_formKeyValidator     = $formKeyValidator;
        $this->wishlistProvider      = $wishlistProvider;
        $this->quantityProcessor     = $quantityProcessor;
        $this->resultPageFactory     = $resultPageFactory;
        $this->wishlistBlock         = $wishlistBlock;
        $this->wishlistHelper        = $wishlistHelper;
        $this->itemFactory           = $itemFactory;
        $this->logger                = $logger;
        $this->escaper               = $escaper;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
        $this->helperData            = $helperData;

        parent::__construct($context);
    }

    /**
     * Update wishlist item comments
     *
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $result = [
                'error'   => true,
                'backUrl' => $this->_url->getUrl('wishlist/index/')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $result = [
                'error'   => true,
                'message' => __('Page not found.')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $categoryId = $this->getRequest()->getParam('fromCategoryId');
        $post       = $this->getRequest()->getPostValue();
        if ($post && isset($post['description']) && is_array($post['description'])) {
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                /**
                 * @var Item $item
                 */
                $item = $this->itemFactory->create()->load($itemId);
                if ($item->getWishlistId() !== $wishlist->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string) $description;

                if ($description == $this->wishlistHelper->defaultCommentString()) {
                    $description = '';
                } elseif (!strlen($description)) {
                    $description = $item->getDescription();
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->quantityProcessor->process($post['qty'][$itemId]);
                }
                if ($qty === null) {
                    $qty = $item->getQty() ?: 1;
                } elseif (0 == $qty) {
                    try {
                        $this->deleteItem($categoryId, $item->getId());
                    } catch (Exception $e) {
                        $this->logger->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t delete item from Wish List right now.'));
                    }
                }

                // Check that we need to save
                if ($item->getDescription() == $description && $item->getQty() == $qty) {
                    continue;
                }
                try {
                    /**
                     * @var WishlistItem $mpWishlistItem
                     */
                    if ($categoryId === 'all') {
                        $this->helperData->updateAllItemQty($item, $qty);
                    } else {
                        $mpWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($item->getId(), $categoryId);
                        if ($mpWishlistItem->getId()) {
                            $allQty    = $item->getQty();
                            $oldQty    = $mpWishlistItem->getQty();
                            $updateQty = $qty;
                            $changeQty = $updateQty - $oldQty;
                            $qty       = $allQty + $changeQty;
                            $mpWishlistItem->setQty($updateQty)->save();
                        }
                    }
                    $item->setDescription($description)->setQty($qty)->save();
                    $updatedItems++;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Can\'t save description %1',
                            $this->escaper->escapeHtml($description)
                        )
                    );
                }
            }

            // save wishlist model for setting date of last update
            if ($updatedItems) {
                try {
                    $wishlist->save();
                    $this->wishlistHelper->calculate();
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__('Can\'t update wish list'));
                }
            }

            if ($this->getRequest()->getParam('save_and_share')) {
                $page   = $this->resultPageFactory->create();
                $result = $page->getLayout()->createBlock(Sharing::class)
                    ->setTemplate('sharing.phtml')->toHtml();

                return $this->getResponse()->representJson(Data::jsonEncode($result));
            }
        }
        $this->messageManager->addSuccessMessage(__('Wishlist Updated'));

        $layout = $this->resultPageFactory->create()->addHandle('wishlist_index_index')->getLayout();

        $updateButton = $layout->getBlock('customer.wishlist.button.update')->toHtml();
        $shareButton  = $layout->getBlock('customer.wishlist.button.share')->toHtml();
        $toCartButton = $layout->getBlock('customer.wishlist.button.toCart')->toHtml();

        $productGrid    = $layout->getBlock('customer.wishlist.items')
            ->setItems($this->wishlistBlock->getWishlistItems())
            ->setCategoryId($categoryId)->toHtml();
        $controlButtons = ['update' => $updateButton, 'share' => $shareButton, 'toCart' => $toCartButton];

        $result = [
            'error'          => false,
            'productGrid'    => $productGrid,
            'controlButtons' => $controlButtons,
        ];

        return $this->getResponse()->representJson(Data::jsonEncode($result));
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
