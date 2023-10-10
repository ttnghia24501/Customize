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

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\RewardPoints\Controller\Adminhtml\AbstractTransaction;

/**
 * Class View
 * @package Mageplaza\RewardPoints\Controller\Adminhtml\Transaction
 */
class View extends AbstractTransaction
{
    /**
     * @return Page|ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $transaction = $this->_initTransaction();
        if ($transaction) {
            /** @var Page $resultPage */
            $resultPage->getConfig()->getTitle()->prepend($transaction->getTransactionId() ? __(
                'Transaction #%1',
                $transaction->getTransactionId()
            ) : __('New Transaction'));

            return $resultPage;
        }

        return $this->_redirect('*/*/');
    }
}
