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

namespace Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Helper\Mapping as HelperMapping;

/**
 * Class GoogleShoppingApi
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab
 */
class GoogleShoppingApi extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_ProductFeed::widget/form.phtml';

    /**
     * @var HelperMapping
     */
    protected $helperMapping;

    /**
     * Mapping constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param HelperMapping $helperMapping
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        HelperMapping $helperMapping,
        array $data = []
    ) {
        $this->helperMapping = $helperMapping;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Google Shopping API');
    }

    /**
     * @return Phrase|string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getMappingFields()
    {
        $feed = $this->getFeed();
        if ($feed->getId()) {
            return $this->helperMapping->getMappingFieldsByRule($feed);
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function isEdit()
    {
        $feed = $this->getFeed();

        return $feed->getId() ?: '0';
    }

    /**
     * @return mixed
     */
    public function getFeed()
    {
        return $this->_coreRegistry->registry('mageplaza_productfeed_feed');
    }

    /**
     * @return string
     */
    public function getMappingUrl()
    {
        return $this->getUrl('mpproductfeed/managefeeds/mapping');
    }

    /**
     * @return array|string
     */
    public function getVariables()
    {
        $variables = '{}';
        $feed      = $this->getFeed();
        if ($feed->getId()) {
            $variables = $this->helperMapping->getDefaultVariable();
        }

        return $variables;
    }
}
