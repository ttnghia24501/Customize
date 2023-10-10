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
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
], function ($, $t,modal, uiConfirm, uiAlert) {
    "use strict";

    $.widget('mageplaza.generateFeed', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initGenerateFeedObs();
        },

        initGenerateFeedObs: function () {
            var self = this;

            this.element.click(function (){
                self.initProcessModal();
                self.mpStopRequest = false;
                var prepareGenerateEl = $('#mp_prepare_generated');
                var prepareProductsEl = $('#mp_prepare_products_data');

                prepareGenerateEl.find('.loader-small').show();

                $.ajax({
                    url: self.options.url,
                    method: 'POST',
                    data: {form_key: window.FORM_KEY, step: 'prepare_generate'},
                    success: function (res){
                        if (!res.success) {
                            self.showErrorMessage(res.message ? res.message : res);
                            self.generateModal.closeModal();
                            return;
                        }
                        prepareGenerateEl.find('.index-icon').html('<i class="fa fa-check" style="color: green"></i>');
                        prepareGenerateEl.find('.loader-small').hide();
                        prepareProductsEl.find('.index-process').html('(<span class="current-count">0</span>/<span class="total-count">'
                            + res.product_count + '</span>)');
                        self.prepareProductData();
                    },
                    error: function (e) {
                        self.mpStopRequest = true;
                        self.showErrorMessage(e.responseText);
                        self.generateModal.closeModal();
                    }
                });
            });
        },

        prepareProductData: function () {
            var self = this;
            if(this.mpStopRequest) {
                return;
            }
            var prepareProductsEl = $('#mp_prepare_products_data');
            prepareProductsEl.find('.loader-small').show();

            $.ajax({
                url: self.options.url,
                method: 'POST',
                data: {form_key: window.FORM_KEY, step: 'prepare_product_data'},
                success: function (res){
                    if (!res.success) {
                        self.showErrorMessage(res.message ? res.message : res);
                        self.generateModal.closeModal();
                        return;
                    }

                    prepareProductsEl.find('.index-process .current-count').text(res.product_count);
                    if (res.complete) {
                        prepareProductsEl.find('.index-icon').html('<i class="fa fa-check" style="color: green"></i>');
                        prepareProductsEl.find('.loader-small').hide();
                        self.generateFeed();
                    } else {
                        self.prepareProductData();
                    }
                },
                error: function (e) {
                    self.mpStopRequest = true;
                    self.showErrorMessage(e.responseText);
                    self.generateModal.closeModal();
                }
            });
        },
        generateFeed: function () {
            var self = this;
            if(this.mpStopRequest) {
                return;
            }
            var renderEl = $('#mp_render');
            renderEl.find('.loader-small').show();

            $.ajax({
                url: self.options.url,
                method: 'POST',
                data: {form_key: window.FORM_KEY, step: 'render'},
                success: function (res){
                    if (!res.success) {
                        self.showErrorMessage(res.message ? res.message : res);
                        self.generateModal.closeModal();
                        return;
                    }

                    self.generateModal.setTitle($t('Generate Completed'));
                    self.generateModal.buttons.first().text($t('Ok'));
                    self.generateModal.buttons.first().data('mpIsComplete', true);
                    renderEl.find('.index-icon').html('<i class="fa fa-check" style="color: green"></i>');
                    renderEl.find('.loader-small').hide();
                    $('#feed_tabs_feed_content').html(res.general_html);
                },
                error: function (e) {
                    self.mpStopRequest = true;
                    self.showErrorMessage(e.responseText);
                    self.generateModal.closeModal();
                    history_gridJsObject.reload();
                }
            });
        },
        initProcessModal: function () {
            var html = '', options, self = this;

            html += '<div>';
            html +=
                '<div class="mp-index-item" id="mp_prepare_generated">' +
                '   <div class="index-icon" style="display: inline-block; width: 20px;"></div>' +
                '   <div class="loader-small"></div>' +
                '   <span class="index-title">' + $t('Prepare Generate') + '</span>' +
                '   <span class="index-process"></span> ' +
                '</div>';
            html +=
                '<div class="mp-index-item" id="mp_prepare_products_data">' +
                '   <div class="index-icon" style="display: inline-block; width: 20px;"></div>' +
                '   <div class="loader-small"></div>' +
                '   <span class="index-title">' + $t('Prepare Products Data') + '</span>' +
                '   <span class="index-process"></span>' +
                '</div>';
            html +=
                '<div class="mp-index-item" id="mp_render">' +
                '   <div class="index-icon" style="display: inline-block; width: 20px;"></div>' +
                '   <div class="loader-small"></div>' +
                '   <span class="index-title">' + $t('Generate Feed') + '</span>' +
                '   <span class="index-process"></span> ' +
                '</div>';

            html += '</div>';
            options = {
                'type': 'popup',
                'modalClass': 'mp-generate-popup',
                'title': $t('Generating Feed...'),
                'responsive': true,
                'innerScroll': true,
                'buttons': [{
                    text: $t('Cancel'),
                    class: 'action',
                    click: function () {
                        var that = this;

                        if (this.buttons.first().data('mpIsComplete')) {
                            that.closeModal();
                        } else {
                            uiConfirm({
                                content: $t('Are you sure to stop generate?'),
                                actions: {
                                    /** @inheritdoc */
                                    confirm: function () {
                                        self.mpStopRequest = true;
                                        that.closeModal();
                                    }
                                }
                            });
                        }
                    }
                }],
                'modalCloseBtnHandler': function () {
                    self.mpStopRequest = true;
                    this.closeModal();
                }
            };
            this.generateModal = modal(options, html);
            this.generateModal.openModal().on('modalclosed', function () {
                $('.mp-generate-popup').remove();
            });
        },
        showErrorMessage: function (mess) {
            uiAlert({
                content: mess
            });
        }
    });

    return $.mageplaza.generateFeed;
});
