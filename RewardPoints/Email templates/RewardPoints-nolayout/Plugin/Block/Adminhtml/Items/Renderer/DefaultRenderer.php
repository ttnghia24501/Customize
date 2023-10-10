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

namespace Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Items\Renderer;

use Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer as ItemsRenderer;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class DefaultRenderer
 * @package Mageplaza\RewardPoints\Plugin\Block\Adminhtml\Items\Renderer
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
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param ItemsRenderer $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(ItemsRenderer $subject, $result)
    {
        if (strpos($result, 'col-total last') !== false
            && ($this->helperData->isEnabled() || $subject->getOrder()->getMpRewardSpent())) {
            $itemRender = $this->helperData->addItemRendererRewardDiscount(
                $subject,
                [
                    'result' => $result,
                    'query' => '//td[@class="col-discount"]'
                ]
            );
            if ($itemRender) {
                return $itemRender;
            }
        }

        return $result;
    }
}
