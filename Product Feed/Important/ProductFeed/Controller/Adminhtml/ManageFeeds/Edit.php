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

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\FeedFactory;

/**
 * Class Edit
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class Edit extends AbstractManageFeeds
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        /** @var Feed $feed */
        $feed = $this->initFeed();
        if (!$feed) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('mpproductfeed/managefeeds/index');

            return $resultRedirect;
        }

        $data = $this->_session->getData('mageplaza_productfeed_feed', true);
        if (!empty($data)) {
            $feed->setData($data);
        }

        $this->coreRegistry->register('mageplaza_productfeed_feed', $feed);

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_ProductFeed::feed');
        $resultPage->getConfig()->getTitle()->set(__('Feed'));
        $title = ($feed->getId() && $feed->getId() !== 'copy') ? __('Edit %1 feed', $feed->getName()) : __('New Feed');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
