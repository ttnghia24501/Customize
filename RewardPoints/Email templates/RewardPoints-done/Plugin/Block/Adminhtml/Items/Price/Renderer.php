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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Items\Price;

use Magento\Weee\Block\Adminhtml\Items\Price\Renderer as PriceRenderer;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Renderer
 * @package Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Items\Price
 */
class Renderer
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Items constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param PriceRenderer $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(PriceRenderer $subject, $result)
    {
        if ($this->helperData->isEnabled() || $subject->getItem()->getMpRewardBaseDiscount()) {
            $itemTotal = $this->helperData->caculateTotal(
                $subject,
                [
                    'result' => $result,
                    'query' => '//td[@class="col-total last"]'
                ]
            );
            if ($itemTotal) {
                return $itemTotal;
            }
        }

        return $result;
    }
}
