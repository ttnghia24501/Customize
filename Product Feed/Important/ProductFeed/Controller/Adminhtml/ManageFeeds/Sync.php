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
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\General;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Config\Source\Status;
use Mageplaza\ProductFeed\Model\FeedFactory;
use Mageplaza\ProductFeed\Model\HistoryFactory;

/**
 * Class Sync
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class Sync extends AbstractManageFeeds
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * Sync constructor.
     *
     * @param FeedFactory $feedFactory
     * @param JsonFactory $jsonFactory
     * @param Data $helperData
     * @param HistoryFactory $historyFactory
     * @param Layout $layout
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        FeedFactory $feedFactory,
        JsonFactory $jsonFactory,
        Data $helperData,
        HistoryFactory $historyFactory,
        Layout $layout,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->helperData  = $helperData;
        $this->layout      = $layout;

        parent::__construct($feedFactory, $coreRegistry, $context);
        $this->historyFactory = $historyFactory;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $feed       = $this->initFeed(true);
        $resultJson = $this->jsonFactory->create();

        if (!$feed->getStatus()) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Please enable the feed to sync.')
            ]);
        }
        if (!$this->helperData->useGoogleShoppingApi($feed)) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('This feed not allowed to sync')
            ]);
        }
        try {
            $result       = $this->helperData->processSyncData($feed);
            $productCount = 0;
            if (isset($product['product_count']) && $product['product_count']) {
                $productCount = $product['product_count'];
            }
            $result['success'] = true;
            if ($this->getRequest()->getParam('step') === 'finish') {
                $history = $this->historyFactory->create();
                $history->setData([
                    'feed_id'         => $feed->getId(),
                    'feed_name'       => $feed->getName(),
                    'status'          => Status::SUCCESS,
                    'type'            => 'sync',
                    'product_count'   => $productCount,
                    'success_message' => __('%1 feed sync successfully', $feed->getName())
                ])->save();
                $result['general_html'] = $this->layout->createBlock(General::class)->toHtml();
            }
        } catch (Exception $e) {
            $history = $this->historyFactory->create();
            $history->setData([
                'feed_id'       => $feed->getId(),
                'feed_name'     => $feed->getName(),
                'status'        => Status::ERROR,
                'type'          => 'sync',
                'product_count' => 0,
                'error_message' => __('%1 feed sync fail', $feed->getName())
            ])->save();
            $result = [
                'success' => false,
                'message' => __('Something went wrong while syncing products. Please try again.')
            ];
        }

        return $resultJson->setData($result);
    }
}