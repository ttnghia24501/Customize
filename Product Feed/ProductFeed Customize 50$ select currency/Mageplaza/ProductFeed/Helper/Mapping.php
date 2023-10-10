<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\ProductFeed\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttributeCollection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\ProductFeed\Model\Feed;

/**
 * Class Mapping
 * @package Mageplaza\ProductFeed\Helper
 */
class Mapping extends AbstractData
{
    /**
     * Match options in {{ }}
     */
    const PATTERN_OPTIONS = '/{{([a-zA-Z_]{0,50})(.*?)}}/si';

    /**
     * @var ProductAttributeCollection
     */
    protected $productAttributeCollection;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var array
     */
    public $primaryAttr = [
        'entity_id',
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
     * Mapping constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ProductAttributeCollection $productAttributeCollection
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductAttributeCollection $productAttributeCollection,
        Escaper $escaper
    ) {
        $this->productAttributeCollection = $productAttributeCollection;
        $this->escaper                    = $escaper;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return string
     */
    public function createMappingFields()
    {
        $mappings = $this->getProductFieldsGoogleShopping();

        return $this->createFields($mappings);
    }

    /**
     * @param array $mappings
     *
     * @return string
     */
    public function createFields($mappings)
    {
        $html = '';
        foreach ($mappings as $key => $mappingData) {
            $html .= $this->createRow($key, $mappingData);
        }

        return $html;
    }

    /**
     * @param string $key
     * @param array $mappingData
     *
     * @return string
     */
    public function createRow($key, $mappingData)
    {
        $html = '<tr>';
        $html .= $this->createLabel($key, $mappingData);
        $html .= $this->createInput($key, 'value', $mappingData);
        $html .= $this->createInput($key, 'default', $mappingData);
        $html .= $this->createInput($key, 'description', $mappingData);
        $html .= $this->createInput($key, 'required', $mappingData);
        $html .= '</tr>';

        return $html;
    }

    /**
     * @param string $key
     * @param array $value
     *
     * @return string
     */
    public function createLabel($key, $value)
    {
        return '<td' . ($value['required'] ? ' class="required"' : '') . '>
                    <label class="admin__field-label mapping-label" for="' . $key . '">
                        <span>' . $value['label'] . '</span>
                    </label>
                </td>';
    }

    /**
     * @param string $key
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function createInput($key, $name, $data)
    {
        $dataInit  = '';
        $nameInput = 'feed[mapping][' . $key . '][' . $name . ']';
        $required  = $data['required'] ? 'required-entry _required' : '';
        $comment   = '';
        if ($name === 'default' || $name === 'value') {
            $comment = '<div class="mp-field-comment">' . __('Accept %1 value.', $data['type']) . '</div>';
        }

        $button = '';
        if ($name === 'value') {
            $button = $this->createButton($key, $data);
            $html   = '<td>
                     <div class="admin__field-control control" style="position: relative" >
                        <textarea id="' . $key . '-' . $name . '"
                        name="' . $nameInput . '"
                        title="' . $data['label'] . '"
                        class="input-text admin__control-text ' . $required . '" ' . $dataInit . '
                        style="width:100%;resize: none;min-height: 35px;height:35px; margin-top: 10%"
                        >' . $data[$name] . '</textarea>' . $comment . $button . '
                     </div>
                </td>';
        } else {
            $html = '<td>
                     <div class="admin__field-control control" style="position: relative" >
                        <input id="' . $key . '-' . $name . '"
                        name="' . $nameInput . '"
                        title="' . $data['label'] . '"
                        type="' . ($name === 'required' ? 'hidden' : 'text') . '"
                        value="' . $data[$name] . '"
                        class="input-text admin__control-text" ' . $dataInit . '
                        style="width:100%; ' . ($name === 'default' ? 'margin-top: 9%' : 'margin-top: 2%') . '"
                        >' . $comment . $button . '
                     </div>
                </td>';
        }

        return $html;
    }

    /**
     * @param string $key
     * @param array $data
     *
     * @return string
     */
    public function createButton($key, $data)
    {
        $title     = __('Insert Variable...');
        $typeName  = 'feed[mapping][' . $key . '][type]';
        $typeValue = $data['type'];

        return '<button id="insert_variable"
                            title="' . $title . '"
                            target="' . $key . '"
                            type="button"
                            style="position: absolute;top: 0;right: -45px;"
                            class="insert_variable">
                                <span>...</span>
                    </button>
                    <input type="hidden" name="' . $typeName . '" value="' . $typeValue . '" />
                ';
    }

    /**
     * @param Feed $feed
     *
     * @return string
     */
    public function getMappingFieldsByRule($feed)
    {
        $mapping     = Data::jsonDecode($feed->getMapping());
        $mappingData = [];
        $feedObject  = $this->getProductFieldsGoogleShopping();

        foreach ($mapping as $key => $dataField) {
            $dataField['label'] = $feedObject[$key]['label'];
            $dataField['type']  = $feedObject[$key]['type'];
            $mappingData[$key]  = $dataField;
        }

        return $this->createFields($mappingData);
    }

    /**
     * @return string
     */
    public function getDefaultVariable()
    {
        $data = $this->getDefaultProductVariable();

        return self::jsonEncode($data);
    }

    /**
     *  =========================================== ZOHO FIELDS ====================================================
     */

    /**
     * @return array
     */
    public function getProductFieldsGoogleShopping()
    {
        return [
            'id'                    => [
                'label'       => __('ID or SKU'),
                'value'       => '{{sku}}',
                'default'     => '',
                'description' => '',
                'required'    => true,
                'type'        => 'string'
            ],
            'title'                 => [
                'label'       => __('Title'),
                'value'       => '{{name}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'brand'                 => [
                'label'       => __('Brand'),
                'value'       => '{{manufacturer}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'description'           => [
                'label'       => __('Description'),
                'value'       => '{{description}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'link'                  => [
                'label'       => __('Link'),
                'value'       => '{{link}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'imageLink'             => [
                'label'       => __('Image Link'),
                'value'       => '{{image_link}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'additionalImageLinks'  => [
                'label'       => __('Additional Image Links'),
                'value'       => '{{images}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'price'                 => [
                'label'       => __('Price'),
                'value'       => '{{final_price}}',
                'default'     => 0,
                'description' => '',
                'required'    => false,
                'type'        => 'object'
            ],
            'availability'          => [
                'label'       => __('Availability'),
                'value'       => '{{quantity_and_stock_status}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'condition'             => [
                'label'       => __('Condition'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'targetCountry'         => [
                'label'       => __('Country of sale'),
                'value'       => 'US',
                'default'     => '',
                'description' => '',
                'required'    => true,
                'type'        => 'string'
            ],
            'contentLanguage'       => [
                'label'       => __('Language'),
                'value'       => 'en',
                'default'     => '',
                'description' => '',
                'required'    => true,
                'type'        => 'string'
            ],
            'channel'               => [
                'label'       => __('Destinations'),
                'value'       => 'online',
                'default'     => '',
                'description' => '',
                'required'    => true,
                'type'        => 'string'
            ],
            'identifierExists'      => [
                'label'       => __('Identifier exists'),
                'value'       => true,
                'default'     => true,
                'description' => '',
                'required'    => false,
                'type'        => 'boolean'
            ],
            'gtin'                  => [
                'label'       => __('GTIN, UPC, EAN, JAN or ISBN'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'mpn'                   => [
                'label'       => __('MPN'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'shippingLabel'         => [
                'label'       => __('Shipping Service Name'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'promotionIds'          => [
                'label'       => __('Promotion Ids'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'customLabel0'          => [
                'label'       => __('Custom label 0'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'customLabel1'          => [
                'label'       => __('Custom label 1'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'customLabel2'          => [
                'label'       => __('Custom label 2'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'customLabel3'          => [
                'label'       => __('Custom label 3'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'customLabel4'          => [
                'label'       => __('Custom label 4'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'googleProductCategory' => [
                'label'       => __('Google Product Category'),
                'value'       => '{{mapping}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'productTypes'          => [
                'label'       => __('Product Type'),
                'value'       => '{{category_path}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'ageGroup'              => [
                'label'       => __('Age Group'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'color'                 => [
                'label'       => __('Color'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'gender'                => [
                'label'       => __('Gender'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'sizes'                 => [
                'label'       => __('Size'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'string'
            ],
            'material'              => [
                'label'       => __('Material'),
                'value'       => '{{material}}',
                'default'     => '',
                'description' => '',
                'required'    => false,
                'type'        => 'object'
            ],
        ];
    }

    /**
     *  =========================================== DEFAULT VALUES ====================================================
     */

    /**
     * @return array
     */
    public function getDefaultProductVariable()
    {
        $productAttributes = $this->productAttributeCollection->getItems();

        $listVariables = $this->getDataAttribute($productAttributes);

        return [
            [
                'code'  => 'general',
                'label' => __('General Product Attributes'),
                'value' => $listVariables['general']
            ],
            [
                'code'  => 'price',
                'label' => __('Price Attributes'),
                'value' => $listVariables['price']
            ],
            [
                'code'  => 'cat',
                'label' => __('Category Attributes'),
                'value' => $listVariables['cat']
            ],
            [
                'code'  => 'img',
                'label' => __('Image Attributes'),
                'value' => $listVariables['img']
            ],
            [
                'code'  => 'stock',
                'label' => __('Stock Attributes'),
                'value' => $listVariables['stock']
            ],
            [
                'code'  => 'other',
                'label' => __('Other Attributes'),
                'value' => $listVariables['other']
            ],
        ];
    }

    /**
     * @param object $attributes
     *
     * @return array
     */
    public function getDataAttribute($attributes)
    {
        $data          = [];
        $generalVars   = [];
        $priceVars     = [];
        $catVars       = [];
        $imgVars       = [];
        $stockVars     = [];
        $otherVars     = [];
        $types         = ['media_image', 'weee', 'swatch_visual', 'swatch_text', 'gallery', 'texteditor'];
        $generalVars[] = [
            'value' => '{{entity_id}}',
            'label' => 'ID'
        ];
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getFrontendInput(), $types, true)) {
                continue;
            }
            $attributeCode = $attribute->getAttributeCode();

            $prefixLabel = '';
            $label       = $prefixLabel . $attribute->getFrontendLabel();
            if ($attribute->getAttributeCode() === 'region_id') {
                $label .= ' ID';
            }

            switch (true) {
                case in_array($attributeCode, $this->primaryAttr, true):
                    $generalVars[] = [
                        'value' => '{{' . $attributeCode . '}}',
                        'label' => $label
                    ];
                    break;
                case in_array($attributeCode, $this->priceTaxAttr, true):
                    $priceVars[] = [
                        'value' => '{{' . $attributeCode . '}}',
                        'label' => $label
                    ];
                    break;
                case in_array($attributeCode, $this->catAttr, true):
                    $catVars[] = [
                        'value' => '{{' . $attributeCode . '}}',
                        'label' => $label
                    ];
                    break;
                case in_array($attributeCode, $this->imgAttr, true):
                    $imgVars[] = [
                        'value' => '{{' . $attributeCode . '}}',
                        'label' => $label
                    ];
                    break;
                case in_array($attributeCode, $this->stockAttr, true):
                    $stockVars[] = [
                        'value' => '{{' . $attributeCode . '}}',
                        'label' => $label
                    ];
                    break;
                default:
                    $otherVars[] = [
                        'value' => '{{' . $attributeCode . '}}',
                        'label' => $label
                    ];
                    break;
            }

            $data = [
                'general' => $generalVars,
                'price'   => $priceVars,
                'cat'     => $catVars,
                'img'     => $imgVars,
                'stock'   => $stockVars,
                'other'   => $otherVars
            ];
        }

        return $data;
    }

    /**
     * @param string $value
     *
     * @return array|mixed
     */
    public function matchData($value)
    {
        preg_match_all(self::PATTERN_OPTIONS, $value, $matches);
        if ($matches && isset($matches[1])) {
            return $matches[1];
        }

        return [];
    }
}
