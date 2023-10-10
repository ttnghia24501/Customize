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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Mageplaza\ProductFeed\Model\Feed;

/**
 * Class GoogleAnalytics
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab
 */
class GoogleAnalytics extends Generic implements TabInterface
{
    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Feed $feed */
        $feed = $this->_coreRegistry->registry('mageplaza_productfeed_feed');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('feed_');
        $form->setFieldNameSuffix('feed');

        $fieldset = $form->addFieldset('ga_base_fieldset', [
            'legend' => __('Google Analytics'),
            'class' => 'fieldset-wide'
        ]);
        $fieldset->addField('campaign_source', 'text', [
            'name' => 'campaign_source',
            'label' => __('Campaign Source'),
            'title' => __('Campaign Source'),
            'note' => __('The referrer: (e.g. google, newsletter). Required if use'),
        ]);
        $fieldset->addField('campaign_medium', 'text', [
            'name' => 'campaign_medium',
            'label' => __('Campaign Medium'),
            'title' => __('Campaign Medium'),
            'note' => __('Marketing medium: (e.g. cpc, banner, email)'),
        ]);
        $fieldset->addField('campaign_name', 'text', [
            'name' => 'campaign_name',
            'label' => __('Campaign Name'),
            'title' => __('Campaign Name'),
            'note' => __('Product, promo code, or slogan (e.g. spring_sale)'),
        ]);
        $fieldset->addField('campaign_term', 'text', [
            'name' => 'campaign_term',
            'label' => __('Campaign Term'),
            'title' => __('Campaign Term'),
            'note' => __('Identify the paid keywords'),
        ]);
        $fieldset->addField('campaign_content', 'text', [
            'name' => 'campaign_content',
            'label' => __('Campaign Content'),
            'title' => __('Campaign Content'),
            'note' => __('Use to differentiate ads'),
        ]);

        $form->addValues($feed->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Google Analytics');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
