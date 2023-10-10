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

namespace Mageplaza\ProductFeed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Model\Feed;

/**
 * Class Edit
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed
 */
class Edit extends Container
{
    /**
     * @var string
     */
    protected $_objectId = 'feed_id';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * constructor
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Initialize Feed edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mageplaza_ProductFeed';
        $this->_controller = 'adminhtml_feed';

        parent::_construct();

        $this->buttonList->add('save-and-continue', [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form'
                    ]
                ]
            ]
        ], -100);

        $feed = $this->coreRegistry->registry('mageplaza_productfeed_feed');

        if ($feed->getId()) {
            $this->buttonList->add('generate', [
                'label' => __('Generate'),
                'class' => 'save',
            ], -90);
            $this->buttonList->add('copy', [
                'label' => __('Copy'),
                'class' => 'save',
                'onclick' => sprintf("location.href = '%s';", $this->getCopyUrl()),
            ], -80);
        }
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        /** @var Feed $feed */
        $feed = $this->coreRegistry->registry('mageplaza_productfeed_feed');
        if ($id = $feed->getId()) {
            return $this->getUrl('*/*/save', ['feed_id' => $id]);
        }

        return parent::getFormActionUrl();
    }

    /**
     * Get generate action URL
     *
     * @return string
     */
    protected function getGenerateUrl()
    {
        $feed = $this->coreRegistry->registry('mageplaza_productfeed_feed');

        return $this->getUrl('*/*/generate', ['feed_id' => $feed->getId()]);
    }

    /**
     * Get duplicate action URL
     *
     * @return string
     */
    protected function getCopyUrl()
    {
        $feed = $this->coreRegistry->registry('mageplaza_productfeed_feed');
        $this->_backendSession->setCopyData($feed->getData());

        return $this->getUrl('*/*/edit', ['feed_id' => 'copy']);
    }
}
