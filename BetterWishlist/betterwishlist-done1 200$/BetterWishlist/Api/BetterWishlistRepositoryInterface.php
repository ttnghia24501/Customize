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
 * @category    Mageplaza
 * @package     Mageplaza_BetterWishlist
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Api;

use Magento\Framework\Webapi\Exception;
use Mageplaza\BetterWishlist\Api\Data\CategoryInterface;

/**
 * Interface BetterWishlistRepositoryInterface
 * @package Mageplaza\BetterWishlist\Api
 */
interface BetterWishlistRepositoryInterface
{
    /**
     * @param int $productId
     * @param string $categoryId
     * @param int $customerId
     *
     * @return boolean
     * @throws Exception
     */
    public function addItemToCategory($productId, $categoryId, $customerId);

    /**
     * @param int $customerId
     * @param boolean $isItems
     *
     * @return CategoryInterface[]
     * @throws Exception
     * @throws \Exception
     */
    public function getAllCategories($customerId, $isItems);

    /**
     * @param int $customerId
     * @param string $categoryId
     * @param boolean $isItems
     *
     * @return CategoryInterface[]
     * @throws Exception
     * @throws \Exception
     */
    public function getCategoryById($customerId, $categoryId, $isItems);

    /**
     * @param CategoryInterface $category
     * @param int $customerId
     *
     * @return CategoryInterface
     * @throws Exception
     */
    public function createCategory(CategoryInterface $category, $customerId);

    /**
     * @param CategoryInterface $category
     * @param int $customerId
     *
     * @return CategoryInterface
     * @throws Exception
     */
    public function editCategory(CategoryInterface $category, $customerId);

    /**
     * @param string $categoryId
     * @param int $customerId
     *
     * @return boolean
     * @throws Exception
     */
    public function deleteCategory($categoryId, $customerId);

    /**
     * @param int $productId
     * @param string $categoryId
     * @param int $customerId
     *
     * @return boolean
     * @throws Exception
     * @throws \Exception
     */
    public function removeItemInCategory($productId, $categoryId, $customerId);
}
