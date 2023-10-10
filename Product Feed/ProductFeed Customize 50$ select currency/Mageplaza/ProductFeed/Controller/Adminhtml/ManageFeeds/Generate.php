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
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\General;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\FeedFactory;

/**
 * Class Generate
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class Generate extends AbstractManageFeeds
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Layout
     */
    private $layout;

    /**
     * Generate constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param Data $helperData
     * @param JsonFactory $jsonFactory
     * @param Layout $layout
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        Data $helperData,
        JsonFactory $jsonFactory,
        Layout $layout
    ) {
        $this->helperData  = $helperData;
        $this->jsonFactory = $jsonFactory;
        $this->layout      = $layout;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $feed       = $this->initFeed(true);
        $resultJson = $this->jsonFactory->create();

        if (!$feed->getStatus()) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Please enable the feed to generate.')
            ]);
        }

        try {
            $result            = $this->helperData->processRequest($feed);
            $result['success'] = true;
            if (isset($result['complete'])
                && $result['complete']
                && $this->getRequest()->getParam('step') === 'render'
            ) {
                $result['general_html'] = $this->layout->createBlock(General::class)->toHtml();
            }
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => __('Something went wrong while generating the Feed. Please try again.')
            ];
        }

        return $resultJson->setData($result);
    }
}
