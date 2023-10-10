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
 * Class UpdateTemplate
 * @package Mageplaza\ProductFeed\Setup\Patch\Data
 */
class UpdateTemplate implements DataPatchInterface
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
        $templateHtml = '<?xml version="1.0"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>M_SELLER_123456</MerchantIdentifier>
  </Header>
  <MessageType>Product</MessageType>
  <PurgeAndReplace>false</PurgeAndReplace>
  <Message>
    {% for product in products %}
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
    {% endfor %}
  </Message>
</AmazonEnvelope>';

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('mageplaza_productfeed_defaulttemplate'),
            ['template_html' => $templateHtml],
            ['name LIKE ?' => 'amazon_marketplace_xml']
        );
        $templateHtml = '<?xml version="1.0" encoding="UTF-8"?>
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
            <review_id>{{ review.review_id }}</review_id>
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
</feed>';

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('mageplaza_productfeed_defaulttemplate'),
            ['template_html' => $templateHtml],
            ['name LIKE ?' => 'google_shopping_review_xml']
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
        return '1.1.0';
    }
}
