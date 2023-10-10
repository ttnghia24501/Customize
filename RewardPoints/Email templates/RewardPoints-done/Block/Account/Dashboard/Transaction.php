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

namespace Mageplaza\RewardPoints\Block\Account\Dashboard;

/**
 * Class Transaction
 * @package Mageplaza\RewardPoints\Block\Account\Dashboard
 */
class Transaction extends \Mageplaza\RewardPoints\Block\Account\Transaction
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->getTransactions()
            ->setPageSize(5);
    }

    /**
     * @return int
     */
    public function getIsRecent()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return '';
    }
}
