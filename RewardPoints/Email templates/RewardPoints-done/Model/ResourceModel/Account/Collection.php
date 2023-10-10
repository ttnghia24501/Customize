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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Model\ResourceModel\Account;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Mageplaza\RewardPoints\Api\Data\RewardCustomerSearchResultInterface;
use Mageplaza\RewardPoints\Model\Account;
use Mageplaza\RewardPoints\Model\ResourceModel\Account as ResourceAccount;

/**
 * Class Collection
 * @package Mageplaza\RewardPoints\Model\ResourceModel\Account
 */
class Collection extends AbstractCollection implements RewardCustomerSearchResultInterface
{
    /**
     * @type string
     */
    protected $_idFieldName = 'reward_id';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(Account::class, ResourceAccount::class);
    }
}
