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
use Magento\Framework\Module\Manager;

/**
 * Class RewardSpendingAuthorize
 * @package Mageplaza\RewardPoints\Observer
 */
class RewardSpendingAuthorize implements ObserverInterface
{
    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * RewardEarningAuthorize constructor.
     *
     * @param Manager $moduleManager
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleManager->isOutputEnabled('Mageplaza_RewardPointsPro')) {
            $observer->getEvent()->getDataResource()->setResource('Mageplaza_RewardPoints::spending_rate_pro');
        }

        return $this;
    }
}
