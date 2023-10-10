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
 * Class RewardDiscount
 * @package Mageplaza\RewardPoints\Model\Sales\Pdf
 */
class RewardDiscount extends PdfReward
{
    /**
     * @var $isUsePointLabel
     */
    protected $isUsePointLabel = false;

    /**
     * @return string
     */
    public function getSortOrder()
    {
        return 670;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->helperData->getDiscountLabel($this->getOrder()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->getSource()->getMpRewardDiscount();
    }
}
