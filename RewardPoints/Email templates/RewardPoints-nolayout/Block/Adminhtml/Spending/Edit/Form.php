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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Spending\Edit;

use Mageplaza\RewardPoints\Model\Source\Direction;

/**
 * Class Form
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Spending\Edit
 */
class Form extends \Mageplaza\RewardPoints\Block\Adminhtml\Earning\Edit\Form
{
    /**
     * @var int spending direction
     */
    protected $currentDirection = Direction::POINT_TO_MONEY;
}
