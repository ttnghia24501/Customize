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

        $feed = $this->coreRegistry->registry('mageplaza_productfeed_feed');

        /** @var Feed $feed */
        if ($feed->getId()) {
            $this->buttonList->add('copy', [
                'label'   => __('Duplicate'),
                'class'   => 'save',
                'onclick' => sprintf("location.href = '%s';", $this->getCopyUrl()),
            ], -100);
            $this->buttonList->add('save-and-continue', [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ], -100);
            if ($feed->getFileType() == 'xml') {
                $this->buttonList->add('sync', [
                    'label' => __('Sync'),
                    'class' => 'save',
                ], -100);
            }
            $this->buttonList->add('generate', [
                'label' => __('Generate'),
                'class' => 'save',
            ], -90);

        } else {
            $this->buttonList->add('save-and-continue', [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ], -100);
            $this->_formScripts[] ='
            require([
                "jquery",
            ], function ($) {
              $(".save").click(function() {
                 $("body").trigger("processStart");
                    if (!$("#edit_form").valid()) {
                        $("body").trigger("processStop");
                    }
              })
            });
        ';
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
