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
use Magento\Framework\Phrase;
use Mageplaza\ProductFeed\Helper\Data;

/**
 * Class File
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer
 */
class File extends AbstractElement
{
    /**
     * Image constructor.
     *
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
        $this->setType('file');
    }

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $value = $this->getValue();

        if (is_array($value) && isset($value['value'])) {
            $path = $value['value'] ? Data::FEED_KEY_FILE_PATH . $value['value'] : '';
            $html = '<div id="mpproductfeed-current-file"><input id="feed_path_key"'
                . ' type="text" class="input-text admin__control-text"'
                . ' value="' . $path . '" placeholder="' . __('Path of key file') . '" readonly /></div>';
        } else {
            $path = $value ? Data::FEED_KEY_FILE_PATH . $value : '';
            $html = '<div id="mpproductfeed-current-file"><input id="feed_path_key"'
                . ' type="text" class="input-text admin__control-text"'
                . ' value="' . $path . '" placeholder="' . __('Path of key file')
                . '" readonly /><div id="add-private-key">...</div></div>';
        }
        $this->setClass('input-file');
        $html .= parent::getElementHtml();
        $html .= $this->_getDeleteCheckbox();

        return $html;
    }

    /**
     * @return mixed|string
     */
    public function toHtml()
    {
        $html = parent::toHtml();
        $html .= '<script type="text/x-magento-init">{"*":{"Mageplaza_ProductFeed/js/feed/upload-key":{"path": "'
            . Data::FEED_KEY_FILE_PATH . '"}}}</script>';

        return $html;
    }

    /**
     * Return html code of delete checkbox element
     *
     * @return string
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ($this->getValue()) {
            $label = (string) new Phrase('Delete File');
            $html  .= '<span class="delete-image">';
            $html  .= '<input type="checkbox"' .
                ' name="' .
                parent::getName() .
                '[delete]" value="1" class="checkbox"' .
                ' id="' .
                $this->getHtmlId() .
                '_delete"' .
                ($this->getDisabled() ? ' disabled="disabled"' : '') .
                '/>';
            $html  .= '<label for="' .
                $this->getHtmlId() .
                '_delete"' .
                ($this->getDisabled() ? ' class="disabled"' : '') .
                '> ' .
                $label .
                '</label>';
            $html  .= $this->_getHiddenInput();
            $html  .= '</span>';
        }

        return $html;
    }

    /**
     * Return html code of hidden element
     *
     * @return string
     */
    protected function _getHiddenInput()
    {
        $value = $this->getValue();

        if (is_array($value) && isset($value['value'])) {
            $html = '<input id="file-path-name" type="hidden" name="'
                . parent::getName() . '[value]" value="' . $value['value'] . '" />';
        } else {
            $html = '<input id="file-path-name" type="hidden" name="'
                . parent::getName() . '[value]" value="' . $value . '" />';
        }

        return $html;
    }

    /**
     * Return name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }
}
