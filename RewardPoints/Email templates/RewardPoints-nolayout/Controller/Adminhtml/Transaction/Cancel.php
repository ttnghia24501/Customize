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

namespace Mageplaza\RewardPoints\Controller\Adminhtml\Transaction;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\RewardPoints\Controller\Adminhtml\AbstractTransaction;

/**
 * Class Cancel
 * @package Mageplaza\RewardPoints\Controller\Adminhtml\Transaction
 */
class Cancel extends AbstractTransaction
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $transaction = $this->_initTransaction();
        if (!$transaction->getId()) {
            $this->messageManager->addErrorMessage(__('The transaction does not exist.'));

            return $this->_redirect('*/*/');
        }

        try {
            $transaction->cancel();

            $this->messageManager->addSuccessMessage(__('The transaction has been canceled successfully.'));

            $this->_redirect('*/*/edit', ['id' => $transaction->getId()]);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while cancelling transaction. Please try again later.'));
        }

        return $this->_redirect('*/*/');
    }
}
