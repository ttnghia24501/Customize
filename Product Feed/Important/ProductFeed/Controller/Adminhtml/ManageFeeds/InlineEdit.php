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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\FeedFactory;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class InlineEdit extends Action
{
    /**
     * JSON Factory
     *
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * Feed Factory
     *
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        FeedFactory $feedFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->feedFactory = $feedFactory;

        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $feedItems = $this->getRequest()->getParam('items', []);
        if (!(!empty($feedItems) && $this->getRequest()->getParam('isAjax'))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $key = array_keys($feedItems);
        $feedId = !empty($key) ? (int)$key[0] : '';
        /** @var Feed $feed */
        $feed = $this->feedFactory->create()->load($feedId);
        try {
            $feedData = $feedItems[$feedId];
            $feed->addData($feedData);
            $feed->save();
        } catch (LocalizedException $e) {
            $messages[] = $this->getErrorWithFeedId($feed, $e->getMessage());
            $error = true;
        } catch (RuntimeException $e) {
            $messages[] = $this->getErrorWithFeedId($feed, $e->getMessage());
            $error = true;
        } catch (Exception $e) {
            $messages[] = $this->getErrorWithFeedId(
                $feed,
                __('Something went wrong while saving the Feed.')
            );
            $error = true;
        }

        return $resultJson->setData(compact('messages', 'error'));
    }

    /**
     * Add Feed id to error message
     *
     * @param Feed $feed
     * @param string $errorText
     *
     * @return string
     */
    public function getErrorWithFeedId(Feed $feed, $errorText)
    {
        return '[Feed ID: ' . $feed->getId() . '] ' . $errorText;
    }
}
