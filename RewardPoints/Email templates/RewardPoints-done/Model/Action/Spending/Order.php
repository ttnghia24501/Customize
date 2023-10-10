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

namespace Mageplaza\RewardPoints\Model\Action\Spending;

use Mageplaza\RewardPoints\Model\Action\Spending;

/**
 * Class Order
 * @package Mageplaza\RewardPoints\Model\Action
 */
class Order extends Spending
{
    const CODE = 'spending_order';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Spending Order');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return $this->getComment($transaction, 'Spend points for purchasing order #%1');
    }
}
