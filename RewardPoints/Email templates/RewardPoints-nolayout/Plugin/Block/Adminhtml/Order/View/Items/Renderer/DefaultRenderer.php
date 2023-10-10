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

namespace Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer as ItemsRenderer;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class DefaultRenderer
 * @package Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Order\View\Items\Renderer
 */
class DefaultRenderer
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
     * @param ItemsRenderer $subject
     * @param $result
     * @return mixed
     */
    public function afterGetColumns(ItemsRenderer $subject, $result)
    {
        if ($this->helperData->isEnabled() || $subject['order']->getMpRewardSpent()) {
            $result['mp-discount'] = 'col-reward-discount';
            $total                 = $result['total'];
            unset($result['total']);
            $result['total']       = $total;
        }

        return $result;
    }
}
