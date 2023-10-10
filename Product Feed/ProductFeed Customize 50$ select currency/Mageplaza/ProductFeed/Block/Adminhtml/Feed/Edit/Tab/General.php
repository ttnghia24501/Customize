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

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer\Time;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Config\Source\CompressFileType;
use Mageplaza\ProductFeed\Model\Config\Source\DaysOfMonth;
use Mageplaza\ProductFeed\Model\Config\Source\DaysOfWeek;
use Mageplaza\ProductFeed\Model\Config\Source\ExecutionMode;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\ResourceModel\History\CollectionFactory;

/**
 * Class General
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var Enabledisable
     */
    protected $enabledisable;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Frequency
     */
    protected $frequency;

    /**
     * @var ExecutionMode
     */
    protected $executionMode;

    /**
     * @var DaysOfWeek
     */
    protected $daysOfWeek;

    /**
     * @var DaysOfMonth
     */
    protected $daysOfMonth;

    /**
     * @var CompressFileType
     */
    protected $compressFileType;

    /**
     * General constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Enabledisable $enableDisable
     * @param Store $systemStore
     * @param Data $helperData
     * @param CollectionFactory $collectionFactory
     * @param Frequency $frequency
     * @param ExecutionMode $executionMode
     * @param DaysOfWeek $daysOfWeek
     * @param DaysOfMonth $daysOfMonth
     * @param CompressFileType $compressFileType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Enabledisable $enableDisable,
        Store $systemStore,
        Data $helperData,
        CollectionFactory $collectionFactory,
        Frequency $frequency,
        ExecutionMode $executionMode,
        DaysOfWeek $daysOfWeek,
        DaysOfMonth $daysOfMonth,
        CompressFileType $compressFileType,
        array $data = []
    ) {
        $this->enabledisable     = $enableDisable;
        $this->systemStore       = $systemStore;
        $this->helperData        = $helperData;
        $this->collectionFactory = $collectionFactory;
        $this->frequency         = $frequency;
        $this->executionMode     = $executionMode;
        $this->daysOfWeek        = $daysOfWeek;
        $this->daysOfMonth       = $daysOfMonth;
        $this->compressFileType  = $compressFileType;

        parent::__construct($context, $registry, $formFactory, $data);
    }

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

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('General Information'),
            'class'  => 'fieldset-wide'
        ]);

        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Name'),
            'title'    => __('Name'),
            'required' => true
        ]);

        $fieldset->addField('status', 'select', [
            'name'   => 'status',
            'label'  => __('Status'),
            'title'  => __('Status'),
            'values' => $this->enabledisable->toOptionArray()
        ]);

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock(Element::class);
        $fieldset->addField('store_id', 'select', [
            'name'   => 'store_id',
            'label'  => __('Store Views'),
            'title'  => __('Store Views'),
            'values' => $this->systemStore->getStoreValuesForForm(false, true)
        ])->setRenderer($rendererBlock);

        $fieldset->addField('file_name', 'text', [
            'name'     => 'file_name',
            'label'    => __('File Name'),
            'title'    => __('File Name'),
            'required' => true
        ]);

        $fieldset->addField('compress_file', 'select', [
            'name'   => 'compress_file',
            'label'  => __('Compress File'),
            'title'  => __('Compress File'),
            'values' => $this->compressFileType->toOptionArray()
        ]);

        if (($feedId = $feed->getId()) && ($feed->getId() !== 'copy')) {
            $historyCollection = $this->collectionFactory->create()
                ->addFieldToFilter('feed_id', $feedId)
                ->addFieldToFilter('type', ['in' => ['cron', 'manual']])
                ->setOrder('created_at', 'desc');
            if ($historyCollection->getSize()) {
                $fieldset->addField('feed_id', 'hidden', ['name' => 'feed_id']);

                $history = $historyCollection->getFirstItem();
                $fileUrl = $this->getUrl(
                    'mpproductfeed/managefeeds/download',
                    ['feed_id' => $feedId]
                );

                $fieldset->addField('file_url', 'link', [
                    'name'  => 'file_url',
                    'href'  => $fileUrl,
                    'label' => __('Generated File URL'),
                    'title' => __('Generated File URL'),
                    'value' => $this->helperData->getFileUrl($history->getFile())
                ]);
                $fieldset->addField('product_count', 'label', [
                    'name'  => 'product_count',
                    'label' => __('Number of exported Products'),
                    'title' => __('Number of exported Products'),
                    'value' => $history->getProductCount()
                ]);
                $fieldset->addField('generated_on', 'label', [
                    'name'  => 'generated_on',
                    'label' => __('Generated On'),
                    'title' => __('Generated On'),
                    'value' => $this->helperData->convertToLocaleTime($history->getCreatedAt()),
                ]);
                $fieldset->addField('error_message', 'label', [
                    'name'               => 'error_message',
                    'value'              => $history->getErrorMessage(),
                    'after_element_html' => '<style>.field-error_message{color: red}</style>'
                ]);
            }
        }

        $generateFieldset = $form->addFieldset('delivery_fieldset', [
            'legend' => __('Generate Config'),
            'class'  => 'fieldset-wide'
        ]);

        $executionMode     = $generateFieldset->addField('execution_mode', 'select', [
            'name'   => 'execution_mode',
            'label'  => __('Execution Mode'),
            'title'  => __('Execution Mode'),
            'values' => [
                'manual' => __('Manual'),
                'cron'   => __('Cron'),
            ],
            'note'   => __('Select <b>Cron</b> to generate the feed automatically. Select <b>Manual</b> to generate the feed manually'),
        ]);
        $frequency         = $generateFieldset->addField('frequency', 'select', [
            'name'   => 'frequency',
            'label'  => __('Frequency'),
            'title'  => __('Frequency'),
            'values' => $this->frequency->toOptionArray(),
            'note'   => __('How often the feed is generated')
        ]);
        $cronRunDayOfWeek  = $generateFieldset->addField('cron_run_day_of_week', 'select', [
            'name'   => 'cron_run_day_of_week',
            'label'  => __('Day'),
            'title'  => __('Day'),
            'values' => $this->daysOfWeek->toOptionArray(),
            'note'   => __('Day of week')
        ]);
        $cronRunDayOfMonth = $generateFieldset->addField('cron_run_day_of_month', 'select', [
            'name'   => 'cron_run_day_of_month',
            'label'  => __('Date'),
            'title'  => __('Date'),
            'values' => $this->daysOfMonth->toOptionArray(),
            'note'   => __('Date of month')
        ]);
        $cronRunTime       = $generateFieldset->addField('cron_run_time', Time::class, [
            'name'  => 'cron_run_time',
            'label' => __('Cron Run Time'),
            'title' => __('Cron Run Time'),
            'note'  => __('Time zone UTC')
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap($frequency->getHtmlId(), $frequency->getName())
                ->addFieldMap($cronRunTime->getHtmlId(), $cronRunTime->getName())
                ->addFieldMap($executionMode->getHtmlId(), $executionMode->getName())
                ->addFieldMap($cronRunDayOfWeek->getHtmlId(), $cronRunDayOfWeek->getName())
                ->addFieldMap($cronRunDayOfMonth->getHtmlId(), $cronRunDayOfMonth->getName())
                ->addFieldDependence($frequency->getName(), $executionMode->getName(), 'cron')
                ->addFieldDependence($cronRunTime->getName(), $executionMode->getName(), 'cron')
                ->addFieldDependence($cronRunDayOfWeek->getName(), $executionMode->getName(), 'cron')
                ->addFieldDependence($cronRunDayOfWeek->getName(), $frequency->getName(), 'W')
                ->addFieldDependence($cronRunDayOfMonth->getName(), $executionMode->getName(), 'cron')
                ->addFieldDependence($cronRunDayOfMonth->getName(), $frequency->getName(), 'M')
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
        return __('General');
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
     * @return string
     */
    public function toHtml()
    {
        $html = parent::toHtml();

        $generateUrl = $this->getUrl(
            'mpproductfeed/managefeeds/generate',
            ['feed_id' => $this->getRequest()->getParam('feed_id')]
        );
        $syncUrl     = $this->getUrl(
            'mpproductfeed/managefeeds/sync',
            ['feed_id' => $this->getRequest()->getParam('feed_id')]
        );

        $html .= '<script type="text/x-magento-init">{"#generate":{"Mageplaza_ProductFeed/js/feed/generate":{"url":"'
            . $generateUrl . '"}}}</script>' .
            '<script type="text/x-magento-init">{"#sync":{"Mageplaza_ProductFeed/js/feed/sync":{"url":"'
            . $syncUrl . '"}}}</script>';

        return $html;
    }
}
