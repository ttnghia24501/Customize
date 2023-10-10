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

namespace Mageplaza\BetterWishlist\Block\Customer\Wishlist;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\WishlistFactory;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem\Collection;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;

/**
 * Class Category
 *
 * @package Mageplaza\BetterWishlist\Block\Customer\Wishlist
 */
class Category extends Template
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var PostHelper
     */
    protected $postDataHelper;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var MpWishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param WishlistFactory $wishlistFactory
     * @param FormKey $formKey
     * @param WishlistHelper $wishlistHelper
     * @param PostHelper $postDataHelper
     * @param Data $helperData
     * @param CategoryFactory $categoryFactory
     * @param MpWishlistItemFactory $mpWishlistItemFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        WishlistFactory $wishlistFactory,
        FormKey $formKey,
        WishlistHelper $wishlistHelper,
        PostHelper $postDataHelper,
        Data $helperData,
        CategoryFactory $categoryFactory,
        MpWishlistItemFactory $mpWishlistItemFactory,
        array $data = []
    ) {
        $this->customerSession       = $customerSession;
        $this->wishlistFactory       = $wishlistFactory;
        $this->formKey               = $formKey;
        $this->wishlistHelper        = $wishlistHelper;
        $this->postDataHelper        = $postDataHelper;
        $this->helperData            = $helperData;
        $this->categoryFactory       = $categoryFactory;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return Template
     */
    protected function _prepareLayout()
    {
        if ($this->getRequest()->getFullActionName() === 'wishlist_index_index') {
            $this->pageConfig->getTitle()->set(__('My Wish Lists'));
        }

        return parent::_prepareLayout();
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDefaultCategory($storeId = null)
    {
        return $this->helperData->getDefaultCategory($storeId);
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getDefaultCategoryCollection($storeId = 0)
    {
        return $this->helperData->getDefaultCategoryCollection($storeId);
    }

    /**
     * @return AbstractCollection
     */
    public function getCategoryCollection()
    {
        $customerId = $this->customerSession->getId();
        $collection = $this->categoryFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId);

        return $collection;
    }

    /**
     * @return mixed
     */
    public function isShowAllItem()
    {
        return (bool) $this->helperData->showAllItem();
    }

    /**
     * @return mixed
     */
    public function isEnableMultiWishlist()
    {
        return $this->helperData->multiWishlistIsEnabled();
    }

    /**
     * @return mixed
     */
    public function allowCustomerCreateWishlist()
    {
        return $this->helperData->isAllowCustomerCreateWishlist();
    }

    /**
     * @return mixed
     */
    public function getLimitWishlist()
    {
        return (int) ($this->helperData->getLimitWishlist() ?: 10000);
    }

    /**
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function isFontAwesomeEnabled($storeId = null)
    {
        return $this->helperData->isFontAwesomeEnabled($storeId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isRemoveAfterAddToCart()
    {
        return $this->helperData->isRemoveAfterAddToCart($this->_storeManager->getStore()->getId());
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @throws                                       Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function reindex()
    {
        if ($this->customerSession->getIsMpReindex()) {
            return;
        }
        $customerId = $this->customerSession->getCustomerId();

        $wishlist    = $this->wishlistFactory->create()->loadByCustomerId($customerId);
        $categoryIds = $this->helperData->getAllCategoryIds();

        /** @var \Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem $mpWishlistItemResource */
        $mpWishlistItemResource = $this->mpWishlistItemFactory->create()->getResource();
        $mpWishlistItemResource->clearItem($categoryIds);

        /**
         * @var Item $item
         */
        foreach ($wishlist->getItemCollection() as $item) {
            $itemQty = $item->getQty();
            $itemId  = $item->getId();
            /** @var Collection $collection */
            $collection = $this->mpWishlistItemFactory->create()->getCollection()
                ->addFieldToFilter('wishlist_item_id', $itemId);

            $totalQty          = $collection->getTotalQty();
            $diff              = $itemQty - $totalQty;
            $defaultCategoryId = $this->getDefaultCategory()->getId();
            /**
             * @var WishlistItem $defaultWishlistItem
             */
            $defaultWishlistItem = $this->mpWishlistItemFactory->create()->loadItem($itemId, $defaultCategoryId);
            if ($diff > 0) {
                $defaultWishlistItem->addData(
                    [
                        'category_id'      => $defaultCategoryId,
                        'wishlist_item_id' => $itemId,
                        'qty'              => $defaultWishlistItem->getQty() + $diff
                    ]
                )->save();
            } elseif ($diff < 0) {
                if ($defaultWishlistItem->getId()) {
                    $diff = $this->checkQty($defaultWishlistItem, $diff);
                    if ($diff <= 0) {
                        return;
                    }
                }
                if ($diff < 0) {
                    foreach ($collection as $mpWishlistItem) {
                        $diff = $this->checkQty($mpWishlistItem, $diff);
                        if ($diff <= 0) {
                            return;
                        }
                    }
                }
            }
        }
        $this->customerSession->setIsMpReindex(1);
    }

    /**
     * @param WishlistItem $mpWishlistItem
     * @param $diff
     *
     * @return int
     * @throws Exception
     */
    protected function checkQty($mpWishlistItem, $diff)
    {
        $mpItemQty = $mpWishlistItem->getQty();
        if (abs($diff) < $mpItemQty) {
            $mpWishlistItem->setQty($mpWishlistItem->getQty() - abs($diff))->save();

            return 0;
        }

        $mpWishlistItem->delete();
        $diff += $mpItemQty;

        return $diff;
    }

    /**
     * @return string
     */
    public function getAddAllToCartParams()
    {
        return $this->postDataHelper->getPostData(
            $this->getUrl('mpwishlist/customer/allcart'),
            ['wishlist_id' => $this->wishlistHelper->getWishlist()->getId()]
        );
    }

    /**
     * @return mixed
     */
    public function versionCompare()
    {
        return $this->helperData->versionCompare('2.3.1');
    }

    /**
     * @param $string
     * @param bool $escapeSingleQuote
     *
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        return $this->helperData->escapeHtmlAttr($string, $escapeSingleQuote);
    }
}
