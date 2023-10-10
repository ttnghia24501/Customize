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

namespace Mageplaza\RewardPoints\Controller\Rewards;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\RewardPoints\Controller\AbstractRewards;

/**
 * Class SettingPost
 * @package Mageplaza\RewardPoints\Controller\Rewards
 */
class SettingPost extends AbstractRewards
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $accountHelper = $this->helper->getAccountHelper();
        $customer = $accountHelper->getCustomerSession()->getCustomerDataObject();

        try {
            $accountHelper->create($customer, [
                'notification_update' => $this->getRequest()->getParam('notification_update', 0),
                'notification_expire' => $this->getRequest()->getParam('notification_expire', 0)
            ]);

            $this->messageManager->addSuccessMessage(__('Saved email settings successfully.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Something wrong when saving email notifications.'));
        }

        $this->_redirect('*/*/');
    }
}
