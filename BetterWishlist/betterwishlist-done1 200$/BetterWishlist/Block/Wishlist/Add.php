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

namespace Mageplaza\BetterWishlist\Block\Wishlist;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;

/**
 * Class Add
 *
 * @package Mageplaza\BetterWishlist\Block\Wishlist
 */
class Add extends Template
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Add constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param Data $helperData
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Data $helperData,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->helperData      = $helperData;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context, $data);
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
     * @param int $storeId
     *
     * @return array
     */
    public function getDefaultCategoryCollection($storeId = 0)
    {
        return $this->helperData->getDefaultCategoryCollection($storeId);
    }

    /**
     * @return array|AbstractCollection
     */
    public function getCategoryCollection()
    {
        $customerId = $this->customerSession->getId();
        if (!$this->helperData->isAllowCustomerCreateWishlist()) {
            return [];
        }
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
        return (int) $this->helperData->getLimitWishlist();
    }
}
