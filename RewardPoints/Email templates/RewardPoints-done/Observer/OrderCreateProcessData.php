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

namespace Mageplaza\RewardPoints\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class OrderCreateProcessData
 * @package Mageplaza\RewardPoints\Observer
 */
class OrderCreateProcessData implements ObserverInterface
{
    /**
     * Process post data and set usage of Extra Fee into order creation model
     *
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $model = $observer->getEvent()->getOrderCreateModel();
        $data  = $observer->getEvent()->getRequest();
        $quote = $model->getQuote();

        if (isset($data['mp_reward_spend_points']) && isset($data['mp_reward_spend_rateId'])) {
            $quote->setMpRewardSpent((int) $data['mp_reward_spend_points']);
            $quote->setMpRewardApplied($data['mp_reward_spend_rateId']);
        }

        return $this;
    }
}
