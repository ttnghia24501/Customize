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

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Block\Adminhtml\LiquidFilters;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Config\Source\DefaultTemplate;

/**
 * Class TemplateContent
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer
 */
class TemplateContent extends Element
{
    /**
     * @var array
     */
    public $primaryAttr = [
        'attribute_set',
        'description',
        'status',
        'meta_description',
        'meta_keyword',
        'meta_title',
        'entity_id',
        'name',
        'type_id',
        'url',
        'sku',
        'short_description',
        'url_key',
        'visibility'
    ];

    /**
     * @var array
     */
    public $priceTaxAttr = [
        'cost',
        'msrp_display_actual_price_type',
        'price_type',
        'final_price',
        'msrp',
        'minimal_price',
        'price',
        'price_view',
        'regular_price',
        'special_price',
        'special_from_date',
        'special_to_date',
        'tax_class_id',
        'tier_price'
    ];

    /**
     * @var array
     */
    public $catAttr = ['category_ids', 'category.entity_id', 'category', 'category.path'];

    /**
     * @var array
     */
    public $imgAttr = [
        'image',
        'image_label',
        'small_image_label',
        'small_image',
        'swatch_image',
        'thumbnail_label',
        'thumbnail'
    ];

    /**
     * @var array
     */
    public $stockAttr = ['mp_is_in_stock', 'qty', 'quantity_and_stock_status', 'quantity_and_stock_status_qty'];

    /**
     * @var array
     */
    public $otherAttr = [];

    /**
     * @var string $_template
     */
    protected $_template = 'Mageplaza_ProductFeed::feed/template/template_content.phtml';

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var DefaultTemplate
     */
    protected $defaultTemplate;

    /**
     * TemplateContent constructor.
     *
     * @param Context $context
     * @param Attribute $eavAttribute
     * @param Registry $registry
     * @param Data $helperData
     * @param LiquidFilters $liquidFilters
     * @param DefaultTemplate $defaultTemplate
     * @param array $data
     */
    public function __construct(
        Context $context,
        Attribute $eavAttribute,
        Registry $registry,
        Data $helperData,
        LiquidFilters $liquidFilters,
        DefaultTemplate $defaultTemplate,
        array $data = []
    ) {
        $this->registry        = $registry;
        $this->helperData      = $helperData;
        $this->liquidFilters   = $liquidFilters;
        $this->eavAttribute    = $eavAttribute;
        $this->defaultTemplate = $defaultTemplate;

        parent::__construct($context, $data);
    }

    /**
     * @return null
     */
    public function getFieldsMap()
    {
        $feed      = $this->registry->registry('mageplaza_productfeed_feed');
        $fieldsMap = $feed->getFieldsMap();
        if (!$fieldsMap) {
            return null;
        }

        if (strpos($fieldsMap, '\"') !== false) {
            $fieldsMap = str_replace('\"', '&quot;', $fieldsMap);
        }

        return $fieldsMap;
    }

    /**
     * @return array
     */
    public function getEavAttrCollection()
    {
        $collection     = $this->eavAttribute->getCollection()
            ->addFieldToFilter(AttributeSet::KEY_ENTITY_TYPE_ID, 4);
        $attrCollection = [
            'primary'   => ['label' => __('General Product Attributes'), 'values' => []],
            'price_tax' => ['label' => __('Price Attributes'), 'values' => []],
            'cat'       => ['label' => __('Category Attributes'), 'values' => []],
            'image'     => ['label' => __('Image Attributes'), 'values' => []],
            'stock'     => ['label' => __('Stock Attributes'), 'values' => []],
            'other'     => ['label' => __('Other Attributes'), 'values' => []],
        ];

        /** @var \Magento\Eav\Model\Entity\Attribute $item */
        foreach ($collection as $item) {
            switch (true) {
                case in_array($item->getAttributeCode(), $this->primaryAttr, true):
                    $attrCollection['primary']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->priceTaxAttr, true):
                    $attrCollection['price_tax']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->catAttr, true):
                    $attrCollection['cat']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->imgAttr, true):
                    $attrCollection['image']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->stockAttr, true):
                    $attrCollection['stock']['values'][] = $item;
                    break;
                default:
                    $attrCollection['other']['values'][] = $item;
                    break;
            }
        }

        $productLink                           = new DataObject([
            'attribute_id'           => 'mp_pf_link',
            'attribute_code'         => 'link',
            'default_frontend_label' => __('Product Link'),
        ]);
        $finalPrice                            = new DataObject([
            'attribute_id'           => 'mp_pf_final_price',
            'attribute_code'         => 'final_price',
            'default_frontend_label' => __('Final Price'),
        ]);
        $imageLink                             = new DataObject([
            'attribute_id'           => 'mp_pf_image_link',
            'attribute_code'         => 'image_link',
            'default_frontend_label' => __('Product Image Link'),
        ]);
        $categoryPath                          = new DataObject([
            'attribute_id'           => 'mp_pf_category_path',
            'attribute_code'         => 'category_path',
            'default_frontend_label' => __('Product Category Path'),
        ]);
        $productId                             = new DataObject([
            'attribute_id'           => 'mp_pf_product_id',
            'attribute_code'         => 'entity_id',
            'default_frontend_label' => __('Product Id'),
        ]);
        $productQty                            = new DataObject([
            'attribute_id'           => 'mp_pf_product_id',
            'attribute_code'         => 'qty',
            'default_frontend_label' => __('Product Qty'),
        ]);
        $mapping                               = new DataObject([
            'attribute_id'           => 'mp_pf_product_mapping',
            'attribute_code'         => 'mapping',
            'default_frontend_label' => __('Product Mapping'),
        ]);
        $attrCollection['primary']['values'][] = $productLink;
        $attrCollection['primary']['values'][] = $imageLink;
        $attrCollection['primary']['values'][] = $categoryPath;
        $attrCollection['primary']['values'][] = $productId;
        $attrCollection['primary']['values'][] = $mapping;
        $attrCollection['other']['values'][]   = $finalPrice;
        $attrCollection['stock']['values'][]   = $productQty;

        return $attrCollection;
    }

    /**
     * @return array
     */
    public function getModifier()
    {
        return $this->liquidFilters->getFilters();
    }

    /**
     * @return string
     */
    public function getDefaultTemplate()
    {
        return Data::jsonEncode($this->defaultTemplate->toArrayWithType());
    }

    /**
     * @return string
     */
    public function getDeliveryPass()
    {
        $feed = $this->registry->registry('mageplaza_productfeed_feed');

        return $feed->getData('password') ?: '';
    }
}
