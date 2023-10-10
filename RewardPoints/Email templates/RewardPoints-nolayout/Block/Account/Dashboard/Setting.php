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

use Mageplaza\RewardPoints\Block\Account\Dashboard;
use Mageplaza\RewardPoints\Helper\Email;

/**
 * Class Setting
 * @package Mageplaza\RewardPoints\Block\Account
 */
class Setting extends Dashboard
{
    /**
     * @return Email
     */
    public function getEmailHelper()
    {
        return $this->helper->getEmailHelper();
    }

    /**
     * @return mixed
     */
    public function getNotificationUpdate()
    {
        return $this->getAccount()->getNotificationUpdate();
    }

    /**
     * @return mixed
     */
    public function getNotificationExpire()
    {
        return $this->getAccount()->getNotificationExpire();
    }
}
