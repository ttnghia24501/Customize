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

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterWishlist\Api\Data\CategoryInterface;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\ResourceModel\Category as CategoryResource;

/**
 * Class Category
 *
 * @package Mageplaza\BetterWishlist\Model
 */
class Category extends AbstractModel implements CategoryInterface
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_wishlist_user_category';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_wishlist_user_category';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_wishlist_user_category';

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CategoryResource::class);
    }

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param Data $helperData
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        Data $helperData,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->helperData   = $helperData;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param $categoryId
     * @param $customerId
     *
     * @return DataObject
     * @throws NoSuchEntityException
     */
    public function loadByCategoryId($categoryId, $customerId)
    {
        /**
         * @var Category $category
         */
        $category = $this->getCollection()
            ->addFieldToFilter('category_id', $categoryId)
            ->addFieldToFilter('customer_id', $customerId)->getFirstItem();
        if ($category->getId()) {
            $category->setData('is_default', false);

            return $category;
        }

        $defaultCategory = $this->helperData->getDefaultCategoryCollection($this->storeManager->getStore()->getId());
        if (isset($defaultCategory[$categoryId])) {
            return $category->setData(
                [
                    'category_id'   => $categoryId,
                    'customer_id'   => $customerId,
                    'is_default'    => true,
                    'category_name' => $defaultCategory[$categoryId]['name'],
                ]
            );
        }

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryId($value)
    {
        $this->setData(self::CATEGORY_ID, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryName()
    {
        return $this->getData(self::CATEGORY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryName($value)
    {
        $this->setData(self::CATEGORY_NAME, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->getData(self::IS_DEFAULT) ?: false;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDefault($value)
    {
        $this->setData(self::IS_DEFAULT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function setItems($value)
    {
        $this->setData(self::ITEMS, $value);

        return $this;
    }
}
