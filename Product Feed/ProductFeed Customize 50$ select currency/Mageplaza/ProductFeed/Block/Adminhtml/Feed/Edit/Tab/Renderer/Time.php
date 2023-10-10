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

namespace Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Class Time
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer
 */
class Time extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('cron_run_time');
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $name = parent::getName();
        if (strpos($name, '[]') === false) {
            $name .= '[]';
        }

        return $name;
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getElementHtml()
    {
        $this->addClass('select admin__control-select');
        $value_hrs = 0;
        $value_min = 0;
        if ($value = $this->getValue()) {
            $values = explode(',', $value);
            if (is_array($values) && count($values) === 2) {
                [$value_hrs, $value_min] = $values;
            }
        }
        $html = '<input type="hidden" id="' . $this->getHtmlId() . '" ' . $this->_getUiId() . '/>';
        $html .= '<select name="' . $this->getName() . '" style="width:80px" ' .
            $this->serialize($this->getHtmlAttributes()) . $this->_getUiId('hour') . '>' . "\n";
        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ((int)$value_hrs === $i ? 'selected="selected"' : '') . '>'
                . $hour . '</option>';
        }
        $html .= '</select>' . "\n";
        $html .= ':&nbsp;<select name="' . $this->getName() . '" style="width:80px" ' .
            $this->serialize($this->getHtmlAttributes()) . $this->_getUiId('minute') . '>' . "\n";
        for ($i = 0; $i < 60; $i += 30) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ((int)$value_min === $i ? 'selected="selected"' : '') . '>'
                . $hour . '</option>';
        }
        $html .= '</select>' . "\n";

        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
