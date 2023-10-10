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

namespace Mageplaza\RewardPoints\Model\Sales\Pdf;

/**
 * Class Earned
 * @package Mageplaza\RewardPoints\Model\Sales\Pdf
 */
class Earned extends PdfReward
{
    /**
     * @return string
     */
    public function getSortOrder()
    {
        return 650;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Earned');
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->getSource()->getMpRewardEarn();
    }
}
