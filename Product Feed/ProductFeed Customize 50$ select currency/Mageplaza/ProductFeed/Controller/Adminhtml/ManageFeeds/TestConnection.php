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
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\FeedFactory;

/**
 * Class TestConnection
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class TestConnection extends AbstractManageFeeds
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * TestConnection constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param Data $helperData
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        Data $helperData
    ) {
        $this->helperData = $helperData;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $protocol = $this->getRequest()->getParam('protocol');
        $host     = $this->getRequest()->getParam('host');
        $passive  = $this->getRequest()->getParam('passive');
        $user     = $this->getRequest()->getParam('user');
        $pass     = $this->getRequest()->getParam('pass');
        $path     = $this->getRequest()->getParam('path');
        $result   = $this->helperData->testConnection($protocol, $host, $passive, $user, $pass, $path);

        return $this->getResponse()->representJson($result);
    }
}
