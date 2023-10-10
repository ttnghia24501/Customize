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
use Magento\Backend\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\FeedFactory;

/**
 * Class Products
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class Products extends AbstractManageFeeds
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * Products constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Session $backendSession
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        PageFactory $pageFactory,
        Session $backendSession
    ) {
        $this->pageFactory    = $pageFactory;
        $this->backendSession = $backendSession;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $page = $this->pageFactory->create();
        $html = $page->getLayout()
            ->createBlock(\Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer\Products::class)->toHtml();
        if ($this->getRequest()->getParam('loadGrid')) {
            $html = Data::jsonEncode($html);
            $this->backendSession->unsProductFeedData();
        }

        return $this->getResponse()->representJson($html);
    }
}
