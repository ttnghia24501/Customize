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
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer\TemplateContent;
use Mageplaza\ProductFeed\Model\Config\Source\DefaultTemplate;
use Mageplaza\ProductFeed\Model\Config\Source\FieldsAround;
use Mageplaza\ProductFeed\Model\Config\Source\FieldsSeparate;
use Mageplaza\ProductFeed\Model\Config\Source\FileType;
use Mageplaza\ProductFeed\Model\Feed;

/**
 * Class Template
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab
 */
class Template extends Generic implements TabInterface
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var FileType
     */
    protected $fileType;

    /**
     * @var DefaultTemplate
     */
    protected $defaultTemplate;

    /**
     * @var FieldsSeparate
     */
    protected $fieldsSeparate;

    /**
     * @var FieldsAround
     */
    protected $fieldsAround;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * Template constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param FieldFactory $fieldFactory
     * @param Yesno $yesNo
     * @param FileType $fileType
     * @param DefaultTemplate $defaultTemplate
     * @param FieldsSeparate $fieldsSeparate
     * @param FieldsAround $fieldsAround
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        FieldFactory $fieldFactory,
        Yesno $yesNo,
        FileType $fileType,
        DefaultTemplate $defaultTemplate,
        FieldsSeparate $fieldsSeparate,
        FieldsAround $fieldsAround,
        array $data = []
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->fileType = $fileType;
        $this->defaultTemplate = $defaultTemplate;
        $this->fieldsSeparate = $fieldsSeparate;
        $this->fieldsAround = $fieldsAround;
        $this->yesNo = $yesNo;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Feed $rule */
        $feed = $this->_coreRegistry->registry('mageplaza_productfeed_feed');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('feed_');
        $form->setFieldNameSuffix('feed');

        $fieldset = $form->addFieldset('template_fieldset', [
            'legend' => __('Templates'),
            'class' => 'fieldset-wide'
        ]);
        $fileType = $fieldset->addField('file_type', 'select', [
            'name' => 'file_type',
            'label' => __('File Type'),
            'title' => __('File Type'),
            'required' => true,
            'values' => $this->fileType->toOptionArray(),
            'disabled' => $feed->getId() ? 1 : 0,
            'note' => __('Select a file type for the product feed')
        ]);
        if (!$feed->getId()) {
            $fieldset->addField('default_template', 'select', [
                'name' => 'enabled',
                'label' => __('Default Template'),
                'title' => __('Default Template'),
                'values' => $this->defaultTemplate->toOptionArray(),
                'after_element_html' => '<a id="load-template" class="btn">' . __('Load Template') . '</a>',
                'note' => __('Select a template for the product feed')
            ]);
        }

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock(TemplateContent::class);
        $templateHtml = $fieldset->addField('template_html', 'textarea', [
            'name' => 'template_html',
            'label' => __('Template Content'),
            'title' => __('Template Content'),
            'note' => __('Supports <a href="https://shopify.github.io/liquid/" target="_blank">Liquid template</a>')
        ])->setRenderer($rendererBlock);

        $fieldSeparate = $fieldset->addField('field_separate', 'select', [
            'name' => 'field_separate',
            'label' => __('Field Separate'),
            'title' => __('Field Separate'),
            'values' => $this->fieldsSeparate->toOptionArray(),

        ]);
        $fieldAround = $fieldset->addField('field_around', 'select', [
            'name' => 'field_around',
            'label' => __('Field Around By'),
            'title' => __('Field Around By'),
            'values' => $this->fieldsAround->toOptionArray(),

        ]);
        $includeHeader = $fieldset->addField('include_header', 'select', [
            'name' => 'include_header',
            'label' => __('Include Field Header'),
            'title' => __('Include Field Header'),
            'values' => $this->yesNo->toOptionArray(),
        ]);

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()
            ->createBlock(TemplateContent::class)
            ->setTemplate('Mageplaza_ProductFeed::feed/template/fields_map.phtml');
        $fieldMap = $fieldset->addField('fields_map', 'text', [
            'name' => 'fields_map',
            'label' => __('Fields Map'),
            'title' => __('Fields Map'),
        ])->setRenderer($rendererBlock);

        $refField = $this->fieldFactory->create([
            'fieldData' => ['value' => 'csv,txt,tsv,xls', 'separator' => ','],
            'fieldPrefix' => ''
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap($fileType->getHtmlId(), $fileType->getName())
                ->addFieldMap($templateHtml->getHtmlId(), $templateHtml->getName())
                ->addFieldMap($fieldSeparate->getHtmlId(), $fieldSeparate->getName())
                ->addFieldMap($fieldAround->getHtmlId(), $fieldAround->getName())
                ->addFieldMap($includeHeader->getHtmlId(), $includeHeader->getName())
                ->addFieldMap($fieldMap->getHtmlId(), $fieldMap->getName())
                ->addFieldDependence($templateHtml->getName(), $fileType->getName(), 'xml')
                ->addFieldDependence($fieldSeparate->getName(), $fileType->getName(), $refField)
                ->addFieldDependence($fieldAround->getName(), $fileType->getName(), $refField)
                ->addFieldDependence($includeHeader->getName(), $fileType->getName(), $refField)
                ->addFieldDependence($fieldMap->getName(), $fileType->getName(), $refField)
        );

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
        return __('Template');
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

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        $formHtml = parent::getFormHtml();
        $childHtml = $this->getChildHtml();

        return $formHtml . $childHtml;
    }
}
