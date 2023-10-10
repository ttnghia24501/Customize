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

namespace Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Order\Creditmemo\View;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Items as ViewItems;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Items
 * @package Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Order\Invoice\View
 */
class Items
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
     * @param ViewItems $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(ViewItems $subject, $result)
    {
        $mpRewardSpent = is_object($subject) ? $subject->getOrder()->getMpRewardSpent() : $subject['order']->getMpRewardSpent();
        if ($this->helperData->isEnabled() || $mpRewardSpent) {
            $headColumn = $this->helperData->headColumn($subject, $result);
            if ($headColumn) {
                return $headColumn;
            }
        }

        return $result;
    }
}
