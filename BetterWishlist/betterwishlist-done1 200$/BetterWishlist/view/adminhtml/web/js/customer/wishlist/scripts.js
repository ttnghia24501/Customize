// jscs:disable
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
define(
    [
        "jquery",
        "prototype",
        'Magento_Sales/order/create/scripts'
    ], function (jQuery) {
        window.ProductGridAdd = Class.create(AdminOrder, {
            initialize: function ($super, data) {
                $super(data);
                this.productsGridAddUrl = '';
            },
            setProductsGridAddUrl: function (url) {
                this.productsGridAddUrl = url;
            },

            /**
             * Submit configured products to quote
             */
            productGridAddSelected: function () {
                // prepare additional fields and filtered items of products
                var fieldsPrepare = {},
                    itemsFilter   = [],
                    products      = this.gridProducts.toObject(),
                    productId,
                    paramKey,
                    productParamKey;

                if (this.productGridShowButton) {
                    Element.show(this.productGridShowButton);
                }


                for (productId in products) {
                    itemsFilter.push(productId);
                    paramKey = 'item[' + productId + ']';
                    for (productParamKey in products[productId]) {
                        paramKey += '[' + productParamKey + ']';
                        fieldsPrepare[paramKey] = products[productId][productParamKey];
                    }
                }
                this.productConfigureSubmit('product_to_add', fieldsPrepare, itemsFilter);
                productConfigure.clean('wishlist');
                jQuery('.add-wishlist-modal').data('modal').closeModal();
                this.gridProducts = $H({});
                this.productPriceBase = $H({});
            },

            resetProductGridAdd: function () {
                jQuery.ajax(
                    {
                        url: this.productsGridAddUrl,
                        method: 'POST',
                        data: {},
                        success: function (res) {
                            jQuery('#add-wishlist-modal').html(res);
                        },
                        error: function () {
                            jQuery('#add-wishlist-modal').html('')
                        }

                    }
                );
            },

            /**
             * Submit batch of configured products
             *
             * @param listType
             * @param fieldsPrepare
             * @param itemsFilter
             */
            productConfigureSubmit: function (listType, fieldsPrepare, itemsFilter) {
                var categoryId = jQuery('#mp-wishlist-category span').attr('id'),
                    url        = this.loadBaseUrl + '?categoryId=' + categoryId,
                    fields     = [],
                    name;

                // prepare additional fields
                fieldsPrepare = this.prepareParams(fieldsPrepare);
                fieldsPrepare.json = 1;

                for (name in fieldsPrepare) {
                    fields.push(new Element('input', {type: 'hidden', name: name, value: fieldsPrepare[name]}));
                }
                productConfigure.addFields(fields);

                // filter items
                if (itemsFilter) {
                    productConfigure.addItemsFilter(listType, itemsFilter);
                }
                // prepare and do submit
                productConfigure.addListType(listType, {urlSubmit: url});
                productConfigure.setOnLoadIFrameCallback(
                    listType, function (response) {
                        var urlParams = '&mess=' + response.message.mess + '&messType=' + response.message.type;

                        wishlistControl.reload(urlParams);
                        this.resetProductGridAdd();
                    }.bind(this)
                );
                productConfigure.submit(listType);
                // clean
                this.productConfigureAddFields = {};
            },
            prepareParams: function (params) {
                if (!params) {
                    params = {};
                }
                if (!params.customer_id) {
                    params.customer_id = this.customerId;
                }
                if (!params.store_id) {
                    params.store_id = this.storeId;
                }
                if (!params.currency_id) {
                    params.currency_id = this.currencyId;
                }
                if (!params.form_key) {
                    params.form_key = FORM_KEY;
                }
                return params;
            }
        });
    }
);
/* jshint ignore:end */

