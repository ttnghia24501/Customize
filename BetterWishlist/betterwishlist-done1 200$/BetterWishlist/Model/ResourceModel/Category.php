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
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Category
 * @package Mageplaza\BetterWishlist\Model\ResourceModel
 */
class Category extends AbstractDb
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        $connectionName = null
    ) {
        $this->customerSession = $customerSession;

        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_wishlist_user_category', 'id');
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getCategoryIds()
    {
        $customerId = $this->customerSession->getId();
        if (!$customerId) {
            return [];
        }

        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from(
                $this->getMainTable(),
                'category_id'
            );

        return $connection->fetchCol($select);
    }
}
