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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterWishlist\Helper\Data;

/**
 * Class LoadCategory
 *
 * @package Mageplaza\BetterWishlist\Controller\Customer
 */
class LoadCategory extends Action
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * LoadCategory constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Escaper $escaper,
        Data $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->helperData   = $helperData;
        $this->_escaper     = $escaper;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Layout
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $selectHtml                = '';
        $storeId                   = $this->storeManager->getStore()->getId();
        $defaultCategoryCollection = $this->helperData->getDefaultCategoryCollection($storeId);
        $categoryCollection        = $this->helperData->getCategoryCollection();
        $isAllowCustomerCreate     = $this->helperData->isAllowCustomerCreateWishlist($storeId);
        $limitWishlist             = $this->helperData->getLimitWishlist($storeId);
        $isMultiple                = $this->helperData->multiWishlistIsEnabled($storeId);

        if ($isMultiple) {
            foreach ($defaultCategoryCollection as $defaultCat) {
                $class      = $defaultCat->getDefault()
                    ? ' selected class="default mpwishlist-option-select"'
                    : ' class="mpwishlist-option-select"';
                $selectHtml .= '<option value="' . $defaultCat->getId() . '"' .
                    $class
                    . '>' . $defaultCat->getName() . '</option>';
            }
            /**
             * @var \Mageplaza\BetterWishlist\Model\Category $item
             */
            foreach ($categoryCollection as $item) {
                $selectHtml .= '<option value="' . $item->getCategoryId() .
                    '" class="mpwishlist-option-select user-defined">'
                    . $this->_escaper->escapeHtml($item->getCategoryName()) . '</option>';
            }
            if ($isAllowCustomerCreate && $categoryCollection->getSize() < $limitWishlist) {
                $selectHtml .= '<option value="new" class="option-new-wishlist">' . __('New Wishlist') . '</option>';
            }
        } else {
            //not display on frontend
            $selectHtml = '<option value="all" selected>All</option>';
        }

        return $this->getResponse()->representJson(Data::jsonEncode(['selectHtml' => $selectHtml]));
    }
}
