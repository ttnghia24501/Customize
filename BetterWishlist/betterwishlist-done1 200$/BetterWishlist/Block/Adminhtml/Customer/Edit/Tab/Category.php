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

namespace Mageplaza\BetterWishlist\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;

/**
 * Class Category
 *
 * @package Mageplaza\BetterWishlist\Block\Adminhtml\Customer\Edit\Tab
 */
class Category extends Template
{
    /**
     * @var string
     */
    protected $_template = 'customer/wishlist-category.phtml';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CategoryFactory $categoryFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CategoryFactory $categoryFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->registry        = $registry;
        $this->categoryFactory = $categoryFactory;
        $this->helperData      = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helperData->isEnabled();
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
     * @return array
     */
    public function getDefaultCategoryCollection()
    {
        return $this->helperData->getDefaultCategoryCollection();
    }

    /**
     * @return AbstractCollection
     */
    public function getCategoryCollection()
    {
        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
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
        return $this->helperData->multiWishlistIsEnabled() && $this->helperData->isEnabled();
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
