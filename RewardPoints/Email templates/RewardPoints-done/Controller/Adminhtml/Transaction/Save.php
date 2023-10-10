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
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Controller\Adminhtml\AbstractTransaction;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\RewardPoints\Controller\Adminhtml\Transaction
 */
class Save extends AbstractTransaction
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPost('transaction')) {

            $customer = $this->helperData->getAccountHelper()->getCustomerById($data['customer_id_form']);
            if (!$customer->getId()) {
                $this->messageManager->addErrorMessage(__('Customer does not exist.'));

                return $this->_redirect('*/*/');
            }

            try {
                $transaction = $this->helperData->getTransaction()
                    ->createTransaction(Data::ACTION_ADMIN, $customer, new DataObject($data));

                $this->messageManager->addSuccessMessage(__('The transaction has been created successfully.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/view', ['id' => $transaction->getId()]);
                }
            } catch (LocalizedException $localizedException) {
                $this->messageManager->addErrorMessage($localizedException->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while creating the transaction. Please try again later.'));
            }
        }

        return $this->_redirect('*/*/');
    }
}
