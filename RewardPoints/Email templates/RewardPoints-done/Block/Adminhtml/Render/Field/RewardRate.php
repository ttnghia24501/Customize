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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Render\Field;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RewardRate
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Render\Field
 */
class RewardRate extends AbstractElement
{
    /**
     * Get element html
     * @return string
     */
    public function getElementHtml()
    {
        $subject    = $this->getData('subject');
        $rewardRate = $subject->getRewardRate();
        $money      = $rewardRate->getMoney() ?: '';
        $points     = $rewardRate->getPoints() ?: '';
        if ($subject->isEarningRate()) {
            $html = '<div class="reward-input">
                        <input type="text" class="admin__control-text reward-input-money required-entry _required validate-greater-than-zero"  name="reward_rate[money]" value="' . $money . '">
                        <label class="reward-label reward-label-money">' . $this->getCurrencySymbol() . '</label>
        		    </div>';
            $html .= '<div class="reward-input2">
                        <input type="text" class="admin__control-text reward-input-point required-entry _required validate-greater-than-zero"  name="reward_rate[points]" value="' . $points . '">
                        <label class="reward-label reward-label-point">' . __('Point(s)') . '</label>
        		    </div>';
        } else {
            $html = '<div class="reward-input">
           			    <input type="text" class="admin__control-text reward-input-point required-entry _required validate-greater-than-zero"  name="reward_rate[points]" value="' . $points . '">
           			    <label class="reward-label reward-label-point">' . __('Point(s)') . '</label>
        		    </div>';
            $html .= '<div class="reward-input2">
            		    <input type="text" class="admin__control-text reward-input-money required-entry _required validate-greater-than-zero"  name="reward_rate[money]" value="' . $money . '">
            		    <label class="reward-label reward-label-money">' . $this->getCurrencySymbol() . '</label>
                    </div>';
        }
        $html .= '<style type="text/css">
					.reward-input2::before {content: "»";left: -20.1429px;position: absolute;top: -3px;font-size: 25px;}
					.reward-input,.reward-input2,.reward-label{position:relative;float:left}
					.reward-input-money {padding-left: 25px}
					.reward-label-money {position: absolute; top: 2px; left: 5px}
					.reward-label-point {position: absolute; top: 2px; right: 8px}
					.reward-input2{margin-left: 30px;position: relative;}
                    .reward-label{padding-top: 5px;margin-left: 5px; font-weight: 600}
					.clr{clear: both}
				</style>';
        $html .= '<div class="clr"></div>';

        return $html;
    }

    /**
     * @return mixed
     */
    protected function getCurrencySymbol()
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager  = $objectManager->get(StoreManagerInterface::class);
        $currencyCode  = $storeManager->getStore()->getBaseCurrencyCode();
        $currency      = $objectManager->create(Currency::class)
            ->load($currencyCode);

        return $currency->getCurrencySymbol();
    }
}
