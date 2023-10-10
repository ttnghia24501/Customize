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
declare(strict_types=1);

namespace Mageplaza\ProductFeed\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 *
 * Class DefaultTemplate
 * @package Mageplaza\ProductFeed\Setup\Patch\Data
 */
class DefaultTemplate implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $sampleTemplates   = [];
        $sampleTemplates[] = [
            'name'           => 'google_shopping_xml',
            'title'          => 'Google Shopping XML',
            'file_type'      => 'xml',
            'template_html'  => '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
  <channel>
    <title>Google Shoppping Feed</title>
    <link>{{ store.base_url }}</link>
    <description>This is description</description>
    {% for product in products %}
    <item>
      <g:id><![CDATA[{{ product.sku }}]]></g:id>
      <g:title><![CDATA[{{ product.name | strip_html | truncatewords: \'150\' }}]]></g:title>
      <g:description><![CDATA[{{ product.description | strip_html | truncatewords: \'600\' }}]]></g:description>
      <link><![CDATA[{{ product.link }}]]></link>
      <g:image_link><![CDATA[{{ product.image_link }}]]></g:image_link>
      {% for image in product.images %}
        <g:additional_image_link><![CDATA[{{ image.url }}]]></g:additional_image_link>
      {% endfor %}
      <g:condition>New</g:condition>
      <g:availability>{{ product.quantity_and_stock_status }}</g:availability>
      <g:price>{{ product.final_price | price }}</g:price>
      <g:google_product_category><![CDATA[{{ product.mapping }}]]></g:google_product_category>
      <g:product_type><![CDATA[{{ product.category_path }}]]></g:product_type>
      <g:brand><![CDATA[{{ product.manufacturer | ifEmpty: \'DefaultBrand\' }}]]></g:brand>
    </item>
    {% endfor %}
  </channel>
</rss>',
            'field_separate' => null,
            'field_around'   => null,
            'include_header' => null,
            'fields_map'     => null
        ];
        $sampleTemplates[] = [
            'name'           => 'google_shopping_review_xml',
            'title'          => 'Google Shopping Review XML',
            'file_type'      => 'xml',
            'template_html'  => '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation=
 "http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd">
    <version>2.3</version>
    <aggregator>
        <name>Aggregator</name>
    </aggregator>
    <publisher>
        <name>{{ store.name | strip_html }}</name>
        <favicon>{{ store.base_url }}favicon.png</favicon>
    </publisher>
    <reviews>
        {% for review in reviews %}
        <review>
            <!-- full sample - includes all optional elements/attributes -->
            <review_id>{{ review.id }}</review_id>
            <reviewer>
                {% if review.nickname %}
                <name>{{ review.nickname | strip_html }}</name>
                {% else %}
                <name is_anonymous="true">Anonymous</name>
                {% endif %}
            </reviewer>
            <review_timestamp>{{ review.created_at }}</review_timestamp>
            <title>{{ review.title | strip_html }}</title>
            <content><![CDATA[{{ review.detail | strip_html }}]]></content>
            <review_url type="singleton">{{ review.url }}</review_url>
            <ratings>
                <overall min="1" max="10">{{ review.rating }}</overall>
            </ratings>
            <products>
                <product>
                    <product_ids>
                        <skus>
                            <sku><![CDATA[{{ review.product.sku }}]]></sku>
                        </skus>
                        <brands>
                            <brand><![CDATA[{{ review.product.manufacturer | ifEmpty: \'DefaultBrand\' }}]]></brand>
                        </brands>
                    </product_ids>
                    <product_name>{{ review.product.name | escape }}</product_name>
                    <product_url><![CDATA[{{ review.product.url }}]]></product_url>
                </product>
            </products>
            <is_spam>false</is_spam>
            <collection_method>post_fulfillment</collection_method>
        </review>
        {% endfor %}
    </reviews>
</feed>',
            'field_separate' => null,
            'field_around'   => null,
            'include_header' => null,
            'fields_map'     => null
        ];
        $sampleTemplates[] = [
            'name'           => 'amazon_inventory_xml',
            'title'          => 'Amazon Inventory XML',
            'file_type'      => 'xml',
            'template_html'  => '<?xml version="1.0" encoding="utf-8" ?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>M_SELLER_123456</MerchantIdentifier>
  </Header>
  <MessageType>Inventory</MessageType>
  <Message>
    {% for product in products %}
    <MessageID>{{ product.entity_id }}</MessageID>
    <OperationType>Update</OperationType>
    <Inventory>
      <SKU><![CDATA[{{ product.sku }}]]></SKU>
      <Quantity>{{ product.qty }}</Quantity>
      <FulfillmentLatency>3</FulfillmentLatency>
    </Inventory>
    {% endfor %}
  </Message>
</AmazonEnvelope>',
            'field_separate' => null,
            'field_around'   => null,
            'include_header' => null,
            'fields_map'     => null
        ];
        $sampleTemplates[] = [
            'name'           => 'amazon_marketplace_xml',
            'title'          => 'Amazon Marketplace XML',
            'file_type'      => 'xml',
            'template_html'  => '<?xml version="1.0"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>M_SELLER_123456</MerchantIdentifier>
  </Header>
  <MessageType>Product</MessageType>
  <PurgeAndReplace>false</PurgeAndReplace>
  <Message>
    <MessageID>1</MessageID>
    <OperationType>Update</OperationType>
    <Product>
      <SKU><![CDATA[{{ product.sku }}]]></SKU>
      <ProductTaxCode>A_GEN_TAX</ProductTaxCode>
      <LaunchDate>2014-04-22T04:00:00</LaunchDate>
      <Condition>
        <ConditionType>New</ConditionType>
      </Condition>
      <DescriptionData>
        <Title><![CDATA[{{ product.name}}]]></Title>
        <Brand><![CDATA[{{ product.manufacturer | ifEmpty: \'DefaultBrand\' }}]]></Brand>
        <Description>{{ product.description | strip_html }}</Description>
        <BulletPoint>Clothes</BulletPoint>
        <ItemDimensions>
          <Weight unitOfMeasure="LB">{{ product.weight | ceil }}</Weight>
        </ItemDimensions>
        <MSRP currency="CAD">{{ product.price | price }}</MSRP>
        <Manufacturer><![CDATA[{{ product.manufacturer | ifEmpty: \'DefaultBrand\' }}]]></Manufacturer>
        <SearchTerms><![CDATA[{{ product.meta_keyword }}]]></SearchTerms>
        <ItemType>handmade-rugs</ItemType>
        <OtherItemAttributes>Rectangular</OtherItemAttributes>
        <TargetAudience>Adults</TargetAudience>
        <TargetAudience>Children</TargetAudience>
        <TargetAudience>Men</TargetAudience>
        <TargetAudience>Women</TargetAudience>
      </DescriptionData>
      <ProductData>
      </ProductData>
    </Product>
  </Message>
</AmazonEnvelope>',
            'field_separate' => null,
            'field_around'   => null,
            'include_header' => null,
            'fields_map'     => null
        ];
        $sampleTemplates[] = [
            'name'           => 'fb_csv',
            'title'          => 'Facebook CSV',
            'file_type'      => 'csv',
            'template_html'  => '',
            'field_separate' => 'comma',
            'field_around'   => 'quotes',
            'include_header' => 1,
            'fields_map'     => '{"1535359311257_257":{"col_name":"id","col_type":"attribute","col_attr_val":"sku","col_pattern_val":"","col_val":"{{ product.sku }}"},"1535359336738_738":{"col_name":"availability","col_type":"attribute","col_attr_val":"quantity_and_stock_status","col_pattern_val":"","col_val":"{{ product.quantity_and_stock_status }}"},"1535359383138_138":{"col_name":"condition","col_type":"pattern","col_attr_val":"0","col_pattern_val":"new","col_val":""},"1535359394224_224":{"col_name":"description","col_type":"attribute","col_attr_val":"description","col_pattern_val":"","col_val":"{{ product.description | strip_html }}","modifiers":{"1535359411350_350":{"value":"strip_html"}}},"1535359417691_691":{"col_name":"image_link","col_type":"attribute","col_attr_val":"image_link","col_pattern_val":"","col_val":"{{ product.image_link }}"},"1535359434434_434":{"col_name":"link","col_type":"attribute","col_attr_val":"link","col_pattern_val":"","col_val":"{{ product.link }}"},"1535359500069_69":{"col_name":"title","col_type":"attribute","col_attr_val":"name","col_pattern_val":"","col_val":"{{ product.name }}"},"1535359506883_883":{"col_name":"price","col_type":"attribute","col_attr_val":"price","col_pattern_val":"","col_val":"{{ product.price }}"},"1535359521867_867":{"col_name":"brand","col_type":"attribute","col_attr_val":"manufacturer","col_pattern_val":"","col_val":"{{ product.manufacturer | ifEmpty: \'Example\' }}","modifiers":{"1535359566895_895":{"value":"ifEmpty","params":["Example"]}}}}',
        ];
        $sampleTemplates[] = [
            'name'           => 'ebay_csv',
            'title'          => 'Ebay CSV',
            'file_type'      => 'csv',
            'template_html'  => '',
            'field_separate' => 'comma',
            'field_around'   => 'quotes',
            'include_header' => 1,
            'fields_map'     => '{"1535384643858_858":{"col_name":"SKU","col_type":"attribute","col_attr_val":"sku","col_pattern_val":"","col_val":"{{ product.sku }}"},"1535384657027_27":{"col_name":"Localized For","col_type":"pattern","col_attr_val":"0","col_pattern_val":"{{ store.locale_code }}","col_val":""},"1535384797895_895":{"col_name":"Title","col_type":"attribute","col_attr_val":"name","col_pattern_val":"","col_val":"{{ product.name }}"},"1535384830972_972":{"col_name":"Product Description","col_type":"attribute","col_attr_val":"description","col_pattern_val":"","col_val":"{{ product.description }}"},"1535384863773_773":{"col_name":"Condition","col_type":"pattern","col_attr_val":"0","col_pattern_val":"NEW","col_val":""},"1535384933191_191":{"col_name":"Picture URL 1","col_type":"attribute","col_attr_val":"image_link","col_pattern_val":"","col_val":"{{ product.image_link }}"}}'
        ];

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('mageplaza_productfeed_defaulttemplate'),
            $sampleTemplates
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.7';
    }
}
