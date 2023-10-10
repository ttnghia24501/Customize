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

namespace Mageplaza\ProductFeed\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\FeedFactory;

/**
 * Class AbstractManageFeeds
 * @package Mageplaza\ProductFeed\Controller\Adminhtml
 */
abstract class AbstractManageFeeds extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mageplaza_ProductFeed::manage_feeds';

    /**
     * Feed model factory
     *
     * @var FeedFactory
     */
    public $feedFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * AbstractManageFeeds constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->feedFactory = $feedFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     *
     * @return bool|Feed
     */
    protected function initFeed($register = false)
    {
        $feedId = $this->getRequest()->getParam('feed_id');

        /** @var Feed $feed */
        $feed = $this->feedFactory->create();

        if ($feedId) {
            if ($feedId === 'copy') {
                $data = $this->_session->getCopyData();
                $feed->setData($data)->setId(null);
            } else {
                $feed = $feed->load($feedId);
                if (!$feed->getId()) {
                    $this->messageManager->addErrorMessage(__('This feed no longer exists.'));

                    return false;
                }
            }
        }
        if ($register) {
            $this->coreRegistry->register('mageplaza_productfeed_feed', $feed);
        }

        return $feed;
    }
}
