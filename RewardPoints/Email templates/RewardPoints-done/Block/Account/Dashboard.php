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

namespace Mageplaza\RewardPoints\Block\Account;

use Magento\Framework\View\Element\Template;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Account;

/**
 * Class Dashboard
 * @method setAccount($account)
 * @method Account getAccount()
 * @package Mageplaza\RewardPoints\Block\Account
 */
class Dashboard extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Dashboard constructor.
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $account = $this->helper->getAccountHelper()->get();
        $this->setAccount($account);
    }

    /**
     * @return mixed|string
     */
    public function getAvailableBalance()
    {
        return $this->getAccount()->getBalanceFormatted();
    }

    /**
     * @return mixed|string
     */
    public function getTotalEarnedPoints()
    {
        return $this->getAccount()->getTotalEarningPoints(true);
    }

    /**
     * @return mixed|string
     */
    public function getTotalSpentPoints()
    {
        return $this->getAccount()->getTotalSpendingPoints(true);
    }

    /**
     * @param $point
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatPoint($point)
    {
        return $this->helper->getPointHelper()->format($point, false);
    }

    /**
     * @return mixed
     */
    public function getPointLabel()
    {
        return $this->helper->getPointHelper()->getPointLabel();
    }

    /**
     * @param $price
     *
     * @return float
     */
    public function convertPrice($price)
    {
        return $this->helper->convertPrice($price);
    }

    /**
     * Get reward account status
     *
     * @return mixed
     */
    public function isRewardAccountActive()
    {
        return $this->helper->isRewardAccountActive();
    }
}
