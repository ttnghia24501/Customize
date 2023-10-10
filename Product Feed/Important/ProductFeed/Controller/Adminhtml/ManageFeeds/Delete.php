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
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Model\Feed;

/**
 * Class Delete
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds
 */
class Delete extends AbstractManageFeeds
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var Feed $feed */
        $feed = $this->initFeed();
        if ($feed->getId()) {
            try {
                $feed->delete();
                $this->messageManager->addSuccessMessage(__('The Feed has been deleted.'));
                $resultRedirect->setPath('mpproductfeed/*/');

                return $resultRedirect;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                // go back to edit form
                $resultRedirect->setPath('mpproductfeed/*/edit', ['feed_id' => $feed->getId()]);

                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('The Feed to delete was not found.'));

        $resultRedirect->setPath('mpproductfeed/*/');

        return $resultRedirect;
    }
}
