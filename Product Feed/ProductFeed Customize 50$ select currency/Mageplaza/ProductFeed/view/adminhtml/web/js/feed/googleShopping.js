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
    'Mageplaza_ProductFeed/js/feed/variables'
], function ($, FeedVariables) {
    "use strict";

    $.widget('mageplaza.feed', {

        _create: function () {
            var self              = this,
                googleShoppingTab = $('li[data-ui-id="mageplaza-productfeed-feed-tabs-tab-item-google-shopping-api"]'),
                fileType          = $('#feed_file_type');

            if (fileType.val() === 'xml') {
                googleShoppingTab.show();
            } else {
                googleShoppingTab.hide();
            }

            fileType.change(function () {
                if ($(this).val() === 'xml') {
                    googleShoppingTab.show();
                } else {
                    googleShoppingTab.hide();
                }
            });

            if (!this.options.isEdit) {
                this.initObserve();
            } else {
                self.initVariables();
            }
        },

        validateWebsite: function () {
            var websiteIdsElement = $('#website_ids');

            $('#website_ids-error').remove();
            if (websiteIdsElement.val() === null) {
                websiteIdsElement.parent().append(this.getErrorElement());

                return false;
            }

            return true;
        },

        getErrorElement: function () {
            return '<label class="mage-error" id="website_ids-error">' + this.options.errorLabel + '</label>';
        },

        /**
         * Init observe
         */
        initObserve: function () {
            this.initGoogleShopping();
        },

        initVariables: function () {
            var self = this;

            $(".insert_variable").click(function () {
                if (self.options.variables) {
                    var fieldTarget = $(this).attr('target');

                    FeedVariables.setEditor(fieldTarget + '-value');
                    FeedVariables.openVariableChooser(self.options.variables);
                }
            });
        },

        initGoogleShopping: function () {
            var self = this;

            $.ajax({
                method: 'POST',
                url: self.options.mappingUrl,
                showLoader: true,
                data: {form_key: window.FORM_KEY},
                success: function (response) {
                    if (response.canMapping) {
                        $("#mapping-body").append(response.mapping_html);
                        $('#general>legend>span').text(self.options.generalLabel);
                        $('.page-columns .side-col').show();
                        $('.admin__field.field.field-status,' +
                            '.admin__field.field.field-name,.admin__field.field.field-priority').show();
                        $('button#save,button#reset,button#save_and_continue').show();
                        $('#container').attr('style', 'width:calc( (100%) * 0.75 - 30px )');
                        self.options.variables = JSON.parse(response.variables);
                        self.initVariables();
                        $('#mp_mapping').trigger('contentUpdated');
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });

    return $.mageplaza.feed;
});
