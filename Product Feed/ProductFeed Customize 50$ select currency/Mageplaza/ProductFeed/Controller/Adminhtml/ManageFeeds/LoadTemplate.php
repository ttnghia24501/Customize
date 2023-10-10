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
use Mageplaza\ProductFeed\Model\DefaultTemplateFactory;
use Mageplaza\ProductFeed\Model\FeedFactory;

/**
 * Class LoadTemplate
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class LoadTemplate extends AbstractManageFeeds
{
    /**
     * @var DefaultTemplateFactory
     */
    protected $defaultTemplate;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * LoadTemplate constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param DefaultTemplateFactory $defaultTemplate
     * @param Data $helperData
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        DefaultTemplateFactory $defaultTemplate,
        Data $helperData
    ) {
        $this->defaultTemplate = $defaultTemplate;
        $this->helperData = $helperData;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $name = $this->_request->getParam('name');
        $defaultTemplate = $this->defaultTemplate->create()->load($name, 'name');

        return $this->getResponse()->representJson(Data::jsonEncode($defaultTemplate));
    }
}
