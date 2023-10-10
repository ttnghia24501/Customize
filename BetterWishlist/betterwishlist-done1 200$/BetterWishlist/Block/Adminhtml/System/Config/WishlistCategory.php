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
 * @category  Mageplaza
 * @package   Mageplaza_BetterWishlist
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Mageplaza\BetterWishlist\Helper\Data;

/**
 * Class WishlistCategory
 *
 * @package Mageplaza\BetterWishlist\Block\Adminhtml\System\Config
 * @method  hasStores()
 */
class WishlistCategory extends Field
{
    /**
     * @var string
     */
    protected $_template = 'system/config/default-category.phtml';

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var
     */
    protected $_element;

    /**
     * WishlistCategory constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }

        return $this->_getData('stores');
    }

    /**
     * @return array
     */
    public function getOptionValues()
    {
        $values          = [];
        $defaultWishlist = $this->helperData->getDefaultWishlist();
        $options         = $defaultWishlist ? Data::jsonDecode($defaultWishlist) : [];
        if (!empty($options['option'])) {
            $values = $this->_prepareOptionValues($options);
        }

        return $values;
    }

    /**
     * @param $options
     *
     * @return array
     */
    protected function _prepareOptionValues($options)
    {
        $defaultValues = $options['default'] ?: [];
        $inputType     = 'radio';
        $order         = $options['option']['order'];
        $values        = [];
        foreach ($options['option']['value'] as $id => $option) {
            $bunch = $this->_prepareAttributeOptionValues(
                $id,
                $option,
                $inputType,
                $defaultValues,
                $order[$id]
            );
            foreach ($bunch as $value) {
                $values[] = new DataObject($value);
            }
        }

        return $values;
    }

    /**
     * @param $rowId
     * @param $option
     * @param $inputType
     * @param $defaultValues
     * @param $sortOrder
     *
     * @return array
     */
    protected function _prepareAttributeOptionValues($rowId, $option, $inputType, $defaultValues, $sortOrder)
    {
        $optionId = $rowId;

        $value['checked']    = in_array($optionId, $defaultValues, true) ? 'checked="checked"' : '';
        $value['intype']     = $inputType;
        $value['id']         = $optionId;
        $value['sort_order'] = $sortOrder;

        foreach ($this->getStores() as $store) {
            $storeId                   = $store->getId();
            $value['store' . $storeId] = isset($option[$storeId]) ? $option[$storeId] : '';
        }

        return [$value];
    }

    /**
     * Returns stores sorted by Sort Order
     *
     * @return array
     */
    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort(
                $stores,
                function ($storeA, $storeB) {
                    if ($storeA->getSortOrder() == $storeB->getSortOrder()) {
                        return $storeA->getId() < $storeB->getId() ? -1 : 1;
                    }

                    return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
                }
            );
        }

        return $stores;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;

        return $this->_toHtml();
    }

    /**
     * Render HTML for element's label
     *
     * @param string $scopeLabel
     *
     * @return string
     */
    public function getLabelHtml($scopeLabel = '')
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="' . $scopeLabel . '"' : '';
        $label      = __('Default Wishlist(s)');

        return '<span' . $scopeLabel . '>' . $label . '</span>';
    }

    /**
     * @return mixed
     */
    public function getElement()
    {
        return $this->_element;
    }
}
