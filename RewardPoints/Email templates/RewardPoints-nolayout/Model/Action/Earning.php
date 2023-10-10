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

namespace Mageplaza\RewardPoints\Model\Action;

use Mageplaza\RewardPoints\Model\Action;
use Mageplaza\RewardPoints\Model\Source\ActionType;

/**
 * Class Earning
 * @package Mageplaza\RewardPoints\Model\Action
 */
abstract class Earning extends Action
{
    /**
     * @return int|mixed
     */
    public function getActionType()
    {
        return ActionType::EARNING;
    }

    /**
     * @return null
     */
    protected function getExpirationDate()
    {
        $expireAfter = $this->helper->getSalesPointExpiredAfter($this->getStoreId());
        if ($expireAfter) {
            return $this->helper->getExpirationDate($expireAfter, $this->getStoreId());
        }

        return parent::getExpirationDate();
    }
}
