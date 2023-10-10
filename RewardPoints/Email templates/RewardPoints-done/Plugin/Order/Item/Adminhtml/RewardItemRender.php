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

namespace Mageplaza\RewardPoints\Plugin\Order\Item\Adminhtml;

use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class RewardItemRender
 * @package Mageplaza\RewardPoints\Plugin\Order\Item\Adminhtml
 */
class RewardItemRender
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * TotalItemRender constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param AbstractItems $subject
     * @param callable $proceed
     * @param $item
     * @param $column
     * @param null $field
     *
     * @return bool|string
     */
    public function aroundGetColumnHtml(
        AbstractItems $subject,
        callable $proceed,
        $item,
        $column,
        $field = null
    ) {
        $item = $subject->getItem();
        if ($column === 'mp-discount') {
            $html = $subject->displayPriceAttribute('mp_reward_discount');

            return $html;
        }

        return $proceed($item, $column, $field);
    }

    /**
     * @param AbstractItems $subject
     * @param callable $proceed
     * @param $code
     * @param false $strong
     * @param string $separator
     * @return string
     */
    public function aroundDisplayPriceAttribute(
        AbstractItems $subject,
        callable $proceed,
        $code,
        $strong = false,
        $separator = '<br />'
    ) {
        if ($code == 'mp_reward_discount') {
            return $subject->displayPrices(
                $subject->getPriceDataObject()->getData('mp_reward_base_discount'),
                $subject->getPriceDataObject()->getData($code),
                $strong,
                $separator
            );
        }

        return $proceed($code, $strong, $separator);
    }
}
