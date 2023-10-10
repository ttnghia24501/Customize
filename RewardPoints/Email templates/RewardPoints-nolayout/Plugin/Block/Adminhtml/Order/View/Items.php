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

namespace Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View\Items as ViewItems;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Items
 * @package Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Order\View
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
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param ViewItems $subject
     * @param $result
     * @return array
     */
    public function afterGetColumns(ViewItems $subject, $result)
    {
        if ($this->helperData->isEnabled() || $subject['order']->getMpRewardSpent()) {
            $result['mp-discount'] = 'Reward Discount Amount';
            $total                 = $result['total'];
            unset($result['total']);
            $result['total']       = $total;
        }

        return $result;
    }
}
