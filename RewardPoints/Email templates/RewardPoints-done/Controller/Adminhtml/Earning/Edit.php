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

namespace Mageplaza\RewardPoints\Controller\Adminhtml\Earning;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\RewardPoints\Controller\Adminhtml\Earning;

/**
 * Class Edit
 * @package Mageplaza\RewardPoints\Controller\Adminhtml\Earning
 */
class Edit extends Earning
{
    /**
     * @return Page|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $rewardRate = $this->_initRewardRate();
        if ($rewardRate) {
            /** @var Page $resultPage */
            $resultPage = $this->_initAction();
            $resultPage->getConfig()->getTitle()
                ->prepend($rewardRate->getId() ? __(
                    'Edit Earning Rate #%1',
                    $rewardRate->getId()
                ) : __('Add Earning Rate'));

            return $resultPage;
        }

        return $this->_redirect('*/*/');
    }
}
