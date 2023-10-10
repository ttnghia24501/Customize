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

namespace Mageplaza\BetterWishlist\Model\ResourceModel;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mageplaza\BetterWishlist\Helper\Data;

/**
 * Class WishlistItem
 * @package Mageplaza\BetterWishlist\Model\ResourceModel
 */
class WishlistItem extends AbstractDb
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * WishlistItem constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Data $helperData
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Data $helperData,
        $connectionName = null
    ) {
        $this->customerSession = $customerSession;
        $this->helperData      = $helperData;

        parent::__construct($context, $connectionName);
    }

    /**
     * @param array $ids
     *
     * @return $this
     * @throws LocalizedException
     */
    public function clearItem($ids = [])
    {
        $customerId = $this->customerSession->getId();
        if (empty($ids) || !$customerId) {
            return $this;
        }
        $idsStr = implode("','", $ids);
        $this->getConnection()->delete(
            $this->getMainTable(),
            "category_id NOT IN ('{$idsStr}') OR category_id IS NULL"
        );

        return $this;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_wishlist_item', 'id');
    }

    /**
     * @param AbstractModel $object
     * @param $wishlistItemId
     * @param null $categoryId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function loadItem(AbstractModel $object, $wishlistItemId, $categoryId = null)
    {
        if ($categoryId === null) {
            $categoryId = $this->helperData->getDefaultCategory()->getId();
        }

        $connection = $this->getConnection();
        if ($connection && $wishlistItemId !== null && $categoryId !== null) {
            $idField       = $this->getConnection()
                ->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'wishlist_item_id'));
            $categoryField = $this->getConnection()
                ->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'category_id'));
            $select        = $this->getConnection()->select()->from($this->getMainTable())
                ->where($idField . '=?', $wishlistItemId)
                ->where($categoryField . '=?', $categoryId);
            $data          = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}
