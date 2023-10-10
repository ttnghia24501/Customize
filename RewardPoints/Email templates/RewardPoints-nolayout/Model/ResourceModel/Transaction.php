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

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Source\Status;
use Zend_Db_Expr;

/**
 * Class Transaction
 * @package Mageplaza\RewardPoints\Model\ResourceModel
 */
class Transaction extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_transaction', 'transaction_id');
    }

    /**
     * @param $transaction
     * @param $rewardAccount
     *
     * @return mixed
     * @throws Exception
     */
    public function saveRewardTransaction($transaction, $rewardAccount)
    {
        $this->beginTransaction();
        try {
            $transaction->save();
            $rewardAccount->save();

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $transaction;
    }

    /**
     * @param \Mageplaza\RewardPoints\Model\Transaction $object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function updatePointUsed($object)
    {
        $pointAmount = abs($object->getPointAmountUpdated() ?: $object->getPointAmount());

        $connection = $this->getConnection();
        $selectTrans = $connection->select()
            ->from($this->getMainTable(), ['transaction_id', 'point_amount', 'point_used'])
            ->where('reward_id = ?', $object->getRewardId())
            ->where('point_amount > point_used')
            ->where('status = ?', Status::COMPLETED)
            ->order(new Zend_Db_Expr('ISNULL(expiration_date) ASC, expiration_date ASC'));

        $transAllIds = [];
        foreach ($connection->fetchAll($selectTrans) as $transaction) {
            $availUsed = $transaction['point_amount'] - $transaction['point_used'];
            if ($pointAmount >= $availUsed) {
                $transAllIds[] = $transaction['transaction_id'];
                $pointAmount -= $availUsed;
            } else {
                $connection->update(
                    $this->getMainTable(),
                    ['point_used' => new Zend_Db_Expr($transaction['point_used'] + $pointAmount)],
                    ['transaction_id = ?' => $transaction['transaction_id']]
                );
                break;
            }

            if (!$pointAmount) {
                break;
            }
        }

        if (count($transAllIds)) {
            $connection->update(
                $this->getMainTable(),
                ['point_used' => new Zend_Db_Expr('point_amount')],
                [new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $transAllIds) . ' )')]
            );
        }

        return $this;
    }

    /**
     * @param \Mageplaza\RewardPoints\Model\Transaction $object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function updatePointRemaining($object)
    {
        $pointAmount = abs($object->getPointAmountUpdated() ?: $object->getPointAmount());

        $connection = $this->getConnection();
        $selectTrans = $connection->select()
            ->from($this->getMainTable(), ['transaction_id', 'point_remaining'])
            ->where('reward_id = ?', $object->getRewardId())
            ->where('order_id = ?', $object->getOrderId())
            ->where('action_type = ?', Data::ACTION_TYPE_EARNING)
            ->where('point_remaining > 0');

        $transAllIds = [];
        foreach ($connection->fetchAll($selectTrans) as $transaction) {
            if ($pointAmount >= $transaction['point_remaining']) {
                $transAllIds[] = $transaction['transaction_id'];
                $pointAmount -= $transaction['point_remaining'];
            } else {
                $connection->update(
                    $this->getMainTable(),
                    ['point_remaining' => new Zend_Db_Expr($transaction['point_remaining'] - $pointAmount)],
                    ['transaction_id = ?' => $transaction['transaction_id']]
                );
                break;
            }

            if (!$pointAmount) {
                break;
            }
        }

        if (count($transAllIds)) {
            $connection->update(
                $this->getMainTable(),
                ['point_remaining' => new Zend_Db_Expr('0')],
                [new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $transAllIds) . ' )')]
            );
        }

        return $this;
    }
}
