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

namespace Mageplaza\RewardPoints\Controller\Adminhtml;

use Magento\Framework\DataObject;
use Mageplaza\RewardPoints\Model\Source\Direction;

/**
 * Class Earning
 * @package Mageplaza\RewardPoints\Controller\Adminhtml
 */
abstract class Earning extends AbstractReward
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_RewardPoints::earning_rate';

    /**
     * @return int|mixed
     */
    protected function getDirection()
    {
        return Direction::MONEY_TO_POINT;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        $data = new DataObject();
        $data->setResource(self::ADMIN_RESOURCE);
        $this->_eventManager->dispatch('mp_reward_earning_authorize_resource', ['data_resource' => $data]);

        return $this->_authorization->isAllowed($data->getResource());
    }
}
