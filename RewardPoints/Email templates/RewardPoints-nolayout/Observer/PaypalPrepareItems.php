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
use Magento\Payment\Model\Cart;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class PaypalPrepareItems
 * @package Mageplaza\RewardPoints\Observer
 */
class PaypalPrepareItems implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * PaypalPrepareItems constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helperData = $helper;
    }

    /**
     * Add reward amount to payment discount total
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $salesEntity = $cart->getSalesModel();
        $discount = abs($salesEntity->getDataUsingMethod('mp_reward_base_discount'));
        if ($discount > 0.0001) {
            $cart->addCustomItem(
                $this->helperData->getDiscountLabel($salesEntity->getDataUsingMethod('store_id')),
                1,
                -1.00 * $discount
            );
        }
    }
}
