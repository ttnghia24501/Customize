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
        'jquery',
        'underscore',
        'Magento_Ui/js/modal/modal',
        'mage/translate',
        'Magento_Customer/js/customer-data',
        'mage/url'
    ], function ($, _, modal, $t, customerData, urlBuilder) {
        'use strict';

        var bodyEl             = $('body'),
            toCategoryEl       = $('#add-to-wishlist-modal select'),
            newCatInput        = $('#add-to-wishlist-modal #new-category'),
            toNewWishlistEl    = $('#add-to-wishlist-modal .to-new-wishlist'),
            addToWishlistModal = $('#add-to-wishlist-modal');

        $.widget('mageplaza.wishlistCategory', {
                options: {
                    nameArray: {},
                    loadCatUrl: '',
                    addToWishlistUrl: '',
                    limitWishlist: 5,
                    gotoWishlistUrl: '',
                    qtyInfo: '#qty',
                    currentCatId: null
                },

                _create: function () {
                    this.initNameArray();
                    this.loadCategory();
                    this.toNewWishlistObs();
                    this.addToWishlistObs();
                    this.gotoWishlistObs();
                    this.showPopupObs();
                    addToWishlistModal.trigger('loadCategory');
                },

                initNameArray: function () {
                    var self = this;

                    $('#add-to-wishlist-modal option').each(
                        function () {
                            self.options.nameArray[$(this).val()] = $(this).text();
                        }
                    );
                },

                loadCategory: function () {
                    var self = this;

                    bodyEl.on(
                        'loadCategory',
                        '#add-to-wishlist-modal',
                        function () {
                            $.ajax(
                                {
                                    url: self.options.loadCatUrl,
                                    method: 'POST',
                                    success: function (res) {
                                        var newWishlistOpt = $('.option-new-wishlist');

                                        toCategoryEl.html(res.selectHtml);
                                        if ($('#add-to-wishlist-modal .user-defined').length
                                            >= self.options.limitWishlist) {
                                            newWishlistOpt.hide();
                                        } else {
                                            newWishlistOpt.show();
                                        }
                                        self.initNameArray();
                                        $(self).trigger('change');
                                        addToWishlistModal.trigger('contentUpdated');
                                    }
                                }
                            );
                        }
                    );
                },
                toNewWishlistObs: function () {
                    bodyEl.on('change', '#add-to-wishlist-modal select', function () {
                            if ($(this).val() === 'new') {
                                toNewWishlistEl.show();
                                $('.mfp-wrap').removeAttr("tabindex");
                            } else {
                                toNewWishlistEl.hide();
                            }
                        }
                    );
                },
                showErrorMes: function (mes) {
                    toNewWishlistEl.find('.error-messages').html(
                        '<div class="mage-error admin__field-error" generated="true">' + mes + '</div>'
                    );
                    clearInterval(window.mpCheckInterval);
                    window.mpCheckInterval = setInterval(
                        function () {
                            toNewWishlistEl.find('.error-messages').html('');
                            clearInterval(window.mpCheckInterval);
                        }, 3000
                    );
                },
                addToWishlistObs: function () {
                    var self = this;

                    bodyEl.on('click', '#add-to-wishlist-modal #mc-to-wishlist', function () {
                            var toCategoryId        = toCategoryEl.val(),
                                toCategoryName      = $('#add-to-wishlist-modal select :selected').text(),
                                newCategoryId       = '',
                                viewLabel           = '',
                                date                = new Date(),
                                customizableOptions = [],
                                data                = JSON.parse($('#add-to-wishlist-modal #action-data').val());

                            if (toCategoryId === 'new') {
                                viewLabel = newCatInput.val().trim();
                                if (viewLabel === '') {
                                    self.showErrorMes($t('Please fill in the wishlist name. '));
                                    return;
                                }
                                if (_.values(self.options.nameArray).indexOf(viewLabel) > -1) {
                                    self.showErrorMes($t('The wishlist name already exists. '));
                                    return;
                                }
                                newCategoryId                         = date.getTime() + '_' + date.getMilliseconds();
                                self.options.nameArray[newCategoryId] = viewLabel;
                            }
                            data.type            = 'add';
                            data.toCategoryId    = toCategoryId;
                            data.fromCategoryId  = toCategoryId;
                            data.toCategoryName  = toCategoryName;
                            data.newCategoryId   = newCategoryId;
                            data.newCategoryName = viewLabel;

                            $('.product-custom-option').each(function (index, element) {
                                if ($(element).is('input[type=text]') ||
                                    $(element).is('input[type=email]') ||
                                    $(element).is('input[type=number]') ||
                                    $(element).is('input[type=hidden]') ||
                                    $(element).is('input[type=checkbox]:checked') ||
                                    $(element).is('input[type=radio]:checked') ||
                                    $(element).is('textarea') ||
                                    $('#' + element.id + ' option:selected').length
                                ) {
                                    if ($(element).data('selector') || $(element).attr('name')) {
                                        customizableOptions = self.getCustomizableData(element);
                                    }
                                }
                            });
                            $.each(customizableOptions, function (index, value) {
                                data[index] = value;
                            });

                            if (toCategoryId === 'new') {
                                self.options.currentCatId = newCategoryId;
                            } else {
                                self.options.currentCatId = toCategoryId;
                            }
                            $.ajax(
                                {
                                    url: self.options.addToWishlistUrl,
                                    method: 'POST',
                                    showLoader: true,
                                    data: data,
                                    success: function (res) {
                                        var options    = {
                                                'type': 'popup',
                                                'modalClass': 'add-after-popup',
                                                'responsive': true,
                                                'innerScroll': true,
                                                'buttons': []
                                            },
                                            popup      = $('#add-wishlist-after-notification'),
                                            popupModal = modal(options, popup);

                                        if (res.backUrl) {
                                            window.location.href = res.backUrl;
                                            return;
                                        }
                                        addToWishlistModal.trigger('loadCategory');
                                        customerData.reload(['customer', 'wishlist'], false);

                                        $('#add-wishlist-after-notification .product-detail').html(res.popup);

                                        popupModal.openModal();
                                        clearInterval(window.mpCheckInterval);
                                        window.mpCheckInterval = setInterval(
                                            function () {
                                                popupModal.closeModal();
                                                clearInterval(window.mpCheckInterval);
                                            }, 5000
                                        );

                                        if (res.message) {
                                            $('#add-wishlist-after-notification .messages').html
                                            ('<div class="mage-error admin__field-error" generated="true">'
                                                + res.message + '</div>');
                                        }
                                    },
                                    error: function () {
                                        window.location.reload();
                                    },
                                    complete: function () {
                                        if (addToWishlistModal.data('mageModal')) {
                                            addToWishlistModal.data('mageModal').closeModal();
                                        }
                                        toCategoryEl.val(
                                            $('#add-to-wishlist-modal option.default').val()).trigger('change');
                                    }
                                }
                            );
                        }
                    );
                },
                gotoWishlistObs: function () {
                    var self = this;

                    bodyEl.on('click', '.go-to-wishlist button', function () {
                        window.location.href = self.options.gotoWishlistUrl +
                            (self.options.currentCatId ? '?fromCategoryId=' + self.options.currentCatId : '');
                    });
                },
                showPopupObs: function () {
                    var self = this;

                    bodyEl.on('click', '[data-action="add-to-wishlist"]:not(.updated)', function (e) {
                        var url,
                            params,
                            options,
                            element = $(self.options.qtyInfo);

                        if (_.isUndefined(customerData.get('customer')().firstname)) {
                            url                  = urlBuilder.build('customer/account');
                            window.location.href = url;
                            return;
                        }
                        if (!addToWishlistModal.length || !(element.validation() && element.validation('isValid'))) {
                            return;
                        }
                        e.preventDefault();
                        e.stopPropagation();
                        params          = $(this).data('post');
                        params.data.qty = $(element).val();
                        $('#add-to-wishlist-modal #action-data').val(JSON.stringify(params.data));
                        options = {
                            'type': 'popup',
                            'title': $t('Choose Wishlist Category '),
                            'responsive': true,
                            'innerScroll': true,
                            'buttons': []
                        };
                        if ($('#add-to-wishlist-modal select').children().length === 1) {
                            addToWishlistModal.find('#mc-to-wishlist').trigger('click');
                        } else {
                            modal(options, addToWishlistModal).openModal();
                        }
                    });
                },
                getCustomizableData: function (element) {
                    var data, elementName, elementValue;

                    element      = $(element);
                    data         = {};
                    elementName  = element.data('selector') ? element.data('selector') : element.attr('name');
                    elementValue = element.val();

                    if (element.is('select[multiple]') && elementValue !== null) {
                        if (elementName.substr(elementName.length - 2) === '[]') { //eslint-disable-line eqeqeq
                            elementName = elementName.substring(0, elementName.length - 2);
                        }
                        $.each(elementValue, function (key, option) {
                            data[elementName + '[' + option + ']'] = option;
                        });
                    } else if (elementName.substr(elementName.length - 2) === '[]') { //eslint-disable-line eqeqeq, max-depth
                        elementName = elementName.substring(0, elementName.length - 2);

                        data[elementName + '[' + elementValue + ']'] = elementValue;
                    } else {
                        data[elementName] = elementValue;
                    }

                    return data;
                }
            }
        );
        return $.mageplaza.wishlistCategory;
    }
);
