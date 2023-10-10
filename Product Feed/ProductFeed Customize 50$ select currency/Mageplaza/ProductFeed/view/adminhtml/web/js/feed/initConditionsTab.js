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
define([
    'jquery',
    'mage/translate'
], function ($) {
    "use strict";

    $.widget('mageplaza.initConditionsTab', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initPreviewProduct();
        },

        initPreviewProduct: function () {
            var self = this;

            $('#preview-product-btn').click(function (e) {
                var form = $("form#edit_form");

                e.preventDefault();
                e.stopPropagation();

                if (!form.valid()) return false;

                $.ajax({
                    type: "POST",
                    url: self.options.url,
                    data: form.serialize(),
                    showLoader: true,
                    success: function (res) {
                        var productListEl = $('.product-list');

                        productListEl.html(res);
                        productListEl.trigger('contentUpdated');
                    }
                });
            });
        }
    });

    return $.mageplaza.initConditionsTab;
});
