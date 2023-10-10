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

namespace Mageplaza\RewardPoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\Account as AccountModel;
use Zend_Db_Expr;

/**
 * Class Account
 * @package Mageplaza\RewardPoints\Model\ResourceModel
 */
class Account extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_customer', 'reward_id');
    }

    /**
     * @param AccountModel $account
     * @param string $type
     *
     * @return string
     */
    public function getTotalPointsByType($account, $type)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(
                $this->getTable('mageplaza_reward_transaction'),
                ['total_points' => new Zend_Db_Expr('sum(point_amount)')]
            )
            ->where('status = ?', Status::COMPLETED)
            ->where('action_type = ?', $type)
            ->where('reward_id = ?', $account->getId());

        return $connection->fetchOne($select);
    }
}
