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
    'Magento_Ui/js/modal/alert'
], function ($, $t, modal, uiConfirm, uiAlert) {
    "use strict";

    $.widget('mageplaza.syncProduct', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initSyncProductsObs();
        },

        initSyncProductsObs: function () {
            var self = this;

            this.element.click(function () {
                self.initProcessModal();
                self.mpStopRequest     = false;
                var prepareSyncEl      = $('#mp_prepare_sync');
                var syncProductsDataEl = $('#mp_sync_product_data');

                prepareSyncEl.find('.loader-small').show();

                $.ajax({
                    url: self.options.url,
                    method: 'POST',
                    data: {form_key: window.FORM_KEY, step: 'prepare_sync'},
                    success: function (res) {
                        if (!res.success) {
                            self.showErrorMessage(res.message ? res.message : res);
                            self.syncProduct.closeModal();
                            return;
                        }
                        prepareSyncEl.find('.index-icon').html('<i class="fa fa-check" style="color: green"></i>');
                        prepareSyncEl.find('.loader-small').hide();
                        syncProductsDataEl.find('.index-process').html('(<span class="current-count">0</span>/<span class="total-count">'
                            + res.product_count + '</span>)');
                        self.syncProductData();
                    },
                    error: function (e) {
                        self.mpStopRequest = true;
                        self.showErrorMessage(e.responseText);
                        self.syncProduct.closeModal();
                    }
                });
            });
        },
        syncProductData: function () {
            var self = this;
            if (this.mpStopRequest) {
                return;
            }
            var syncProductsEl = $('#mp_sync_product_data');
            syncProductsEl.find('.loader-small').show();

            $.ajax({
                url: self.options.url,
                method: 'POST',
                data: {form_key: window.FORM_KEY, step: 'sync_product_data'},
                success: function (res) {
                    if (!res.success) {
                        self.showErrorMessage(res.message ? res.message : res);
                        self.syncProduct.closeModal();
                        return;
                    }

                    syncProductsEl.find('.index-process .current-count').text(res.product_count);
                    if (res.complete) {
                        syncProductsEl.find('.index-icon').html('<i class="fa fa-check" style="color: green"></i>');
                        syncProductsEl.find('.loader-small').hide();
                        self.finishSync();
                    } else {
                        self.syncProductData();
                    }
                },
                error: function (e) {
                    self.mpStopRequest = true;
                    self.showErrorMessage(e.responseText);
                    self.syncProduct.closeModal();
                }
            });
        },
        finishSync: function () {
            var self = this;
            if (this.mpStopRequest) {
                return;
            }
            var syncResult = $('#sync_result');
            syncResult.find('.loader-small').show();

            $.ajax({
                url: self.options.url,
                method: 'POST',
                data: {form_key: window.FORM_KEY, step: 'finish'},
                success: function (res) {
                    if (!res.success) {
                        self.showErrorMessage(res.message ? res.message : res);
                        self.syncProduct.closeModal();
                        return;
                    }

                    self.syncProduct.setTitle($t('Sync Completed'));
                    self.syncProduct.buttons.first().text($t('Ok'));
                    self.syncProduct.buttons.first().data('mpIsComplete', true);
                    syncResult.find('.index-icon').html('<i class="fa fa-check" style="color: green"></i>');
                    syncResult.find('.loader-small').hide();
                    $('#feed_tabs_feed_content').html(res.general_html);
                },
                error: function (e) {
                    self.mpStopRequest = true;
                    self.showErrorMessage(e.responseText);
                    self.syncProduct.closeModal();
                    history_gridJsObject.reload();
                }
            });
        },
        initProcessModal: function () {
            var html = '', options, self = this;

            html += '<div>';
            html +=
                '<div class="mp-index-item" id="mp_prepare_sync">' +
                '   <div class="index-icon" style="display: inline-block; width: 20px;"></div>' +
                '   <div class="loader-small"></div>' +
                '   <span class="index-title">' + $t('Prepare Sync') + '</span>' +
                '   <span class="index-process"></span> ' +
                '</div>';
            html +=
                '<div class="mp-index-item" id="mp_sync_product_data">' +
                '   <div class="index-icon" style="display: inline-block; width: 20px;"></div>' +
                '   <div class="loader-small"></div>' +
                '   <span class="index-title">' + $t('Syncing') + '</span>' +
                '   <span class="index-process"></span>' +
                '</div>';
            html +=
                '<div class="mp-index-item" id="sync_result">' +
                '   <div class="index-icon" style="display: inline-block; width: 20px;"></div>' +
                '   <div class="loader-small"></div>' +
                '   <span class="index-title">' + $t('Finished') + '</span>' +
                '   <span class="index-process"></span> ' +
                '</div>';

            html += '</div>';
            options          = {
                'type': 'popup',
                'modalClass': 'mp-sync-popup',
                'title': $t('Syncing Product...'),
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
                                content: $t('Are you sure to stop?'),
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
            this.syncProduct = modal(options, html);
            this.syncProduct.openModal().on('modalclosed', function () {
                $('.mp-sync-popup').remove();
            });
        },
        showErrorMessage: function (mess) {
            uiAlert({
                content: mess
            });
        }
    });

    return $.mageplaza.syncProduct;
});
