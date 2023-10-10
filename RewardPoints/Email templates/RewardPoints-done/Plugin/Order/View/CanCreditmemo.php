<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\RewardPoints\Plugin\Order\View;

use Magento\Sales\Model\Order;

/**
 * Class CanCreditmemo
 * @package Mageplaza\RewardPoints\Plugin\Order\View
 */
class CanCreditmemo
{
    /**
     * @param Order $subject
     */
    public function beforeCanCreditmemo(Order $subject)
    {
        if (in_array($subject->getState(), [
                Order::STATE_PROCESSING,
                Order::STATE_COMPLETE,
                Order::STATE_CLOSED
            ]) && $subject->getMpRewardDiscount() > 0 && $this->validateQty($subject)) {
            $subject->setForcedCanCreditmemo(true);
        }
    }

    /**
     * @param $subject
     *
     * @return bool
     */
    public function validateQty($subject)
    {
        foreach ($subject->getItems() as $item) {
            if ($item->getQtyRefunded() < $item->getQtyOrdered()) {
                return true;
            }
        }

        return false;
    }
}
