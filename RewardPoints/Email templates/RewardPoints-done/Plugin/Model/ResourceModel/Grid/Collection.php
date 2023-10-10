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

namespace Mageplaza\RewardPoints\Plugin\Model\ResourceModel\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection as CustomerGrid;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;
use Zend_Db_Select_Exception;

/**
 * Class Collection
 * @package Mageplaza\RewardPoints\Plugin\Model\ResourceModel\Grid
 */
class Collection
{
    /**
     * Flag to check whether the join query is added or not
     *
     * @var bool $isJoint
     */
    protected $isJoin = false;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Collection constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param CustomerGrid $subject
     * @param $result
     *
     * @return Select
     * @throws Zend_Db_Select_Exception
     */
    public function afterGetSelect(CustomerGrid $subject, $result)
    {
        $rewardCustomerTable = $subject->getTable('mageplaza_reward_customer');
        /** @var $result Select */
        if ($result && $result->getPart('from') && !$subject->getFlag('is_mageplaza_rewardpoints_joined')) {
            $result = $result->joinLeft(
                $rewardCustomerTable,
                "main_table.entity_id = {$rewardCustomerTable}.customer_id",
                [
                    'point_balance' => "{$rewardCustomerTable}.point_balance",
                    'is_active'     => "{$rewardCustomerTable}.is_active",
                ]
            );

            $subject->setFlag('is_mageplaza_rewardpoints_joined', true);
        }

        return $result;
    }

    /**
     * @param CustomerGrid $subject
     * @param callable $proceed
     * @param $field
     * @param $condition
     *
     * @return mixed
     */
    public function aroundAddFieldToFilter(CustomerGrid $subject, callable $proceed, $field, $condition)
    {
        if ($field === 'point_balance' || $field === 'is_active') {
            $rewardCustomerTable = $subject->getTable('mageplaza_reward_customer');
            $field               = $rewardCustomerTable . '.' . $field;
        } else {
            return $proceed($field, $condition);
        }

        return $proceed($field, $condition);
    }

    /**
     * @param CustomerGrid $subject
     */
    public function beforeGetItems(CustomerGrid $subject)
    {
        $actionName = $this->request->getActionName();
        if ($actionName === 'gridToCsv' && !$subject->getFlag('is_mageplaza_rewardpoints_joined')) {
            $rewardCustomerTable = $subject->getTable('mageplaza_reward_customer');
            $subject->getSelect()->joinLeft(
                $rewardCustomerTable,
                "main_table.entity_id = {$rewardCustomerTable}.customer_id",
                [
                    'point_balance' => "{$rewardCustomerTable}.point_balance",
                    'is_active'     => "{$rewardCustomerTable}.is_active",
                ]
            );

            $subject->setFlag('is_mageplaza_rewardpoints_joined', true);
        }
    }
}
