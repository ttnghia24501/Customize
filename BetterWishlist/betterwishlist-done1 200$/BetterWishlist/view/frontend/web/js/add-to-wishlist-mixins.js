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

define([
    'jquery',
    'mage/url',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, urlBuilder, _, customerData, modal, $t) {
    'use strict';

    var bodyEl             = $('body'),
        toNewWishlistEl    = $('#add-to-wishlist-modal .to-new-wishlist'),
        saveBtn            = $('#add-to-wishlist-modal #mc-to-wishlist'),
        catBtn             = $('#add-to-wishlist-modal #save-wishlist-category'),
        toCategoryEl       = $('#add-to-wishlist-modal select'),
        newCatInput        = $('#add-to-wishlist-modal #new-category'),
        addToWishlistModal = $('#add-to-wishlist-modal');

    return function (AddToWishlist) {
        $.widget('mage.addToWishlist', AddToWishlist, {
            bindFormSubmit: function () {
                var action, params,
                    self    = this,
                    element = $('input[type=file].product-custom-option'),
                    form    = $(element).closest('form');

                $('[data-action="add-to-wishlist"]').on('click', function (event) {
                    var url,
                        options;

                    params = $(event.currentTarget).data('post');
                    action = params.action;
                    if (_.isUndefined(customerData.get('customer')().firstname)) {
                        url                  = urlBuilder.build('customer/account');
                        window.location.href = url;
                        return;
                    }
                    if (!addToWishlistModal.length) {
                        return;
                    }

                    event.stopPropagation();
                    event.preventDefault();

                    options = {
                        'type': 'popup',
                        'title': $t('Choose Wishlist Category '),
                        'responsive': true,
                        'innerScroll': true,
                        'buttons': []
                    };
                    modal(options, addToWishlistModal).openModal();
                    catBtn.show();
                    saveBtn.hide();
                });

                bodyEl.on('click', '#add-to-wishlist-modal #save-wishlist-category', function () {

                    var categoryName   = [],
                        data           = [],
                        toCategoryId   = toCategoryEl.val(),
                        toCategoryName = $('#add-to-wishlist-modal select :selected').text(),
                        newCategoryId  = '',
                        viewLabel      = '',
                        date           = new Date(),
                        formData;

                    self.initCategoryName(categoryName);

                    if (toCategoryId === 'new') {
                        viewLabel = newCatInput.val().trim();
                        if (viewLabel === '') {
                            self.showErrorMes($t('Please fill in the wishlist name. '));
                            return;
                        }
                        if (_.values(categoryName).indexOf(viewLabel) > -1) {
                            self.showErrorMes($t('The wishlist name already exists. '));
                            return;
                        }
                        newCategoryId               = date.getTime() + '_' + date.getMilliseconds();
                        categoryName[newCategoryId] = viewLabel;
                        data.newCategoryId          = newCategoryId;
                        data.newCategoryName        = viewLabel;
                    }

                    data.type                 = 'add';
                    data.toCategoryId         = toCategoryId;
                    data.fromCategoryId       = toCategoryId;
                    data.toCategoryName       = toCategoryName;
                    data.option_1_file_action = 'save_new';

                    self.addFormInput(form, data);

                    if (params.data.id) {
                        $('<input>', {type: 'hidden', name: 'id', value: params.data.id}).appendTo(form);
                    }

                    if (params.data.uenc) {
                        action += 'uenc/' + params.data.uenc;
                    }
                    action = action.replace('wishlist/index/add', 'mpwishlist/customer/addtowishlist');
                    addToWishlistModal.find('[data-role="closeBtn"]').trigger('click');
                    // $(form).attr('action', action).submit();
                    formData = new FormData(form[0]);
                    $.ajax({
                        url: action,
                        type: 'POST',
                        data: formData,
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
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                });
            },

            initCategoryName: function (categoryName) {
                $('#add-to-wishlist-modal option').each(
                    function () {
                        categoryName[$(this).val()] = $(this).text().trim();
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

            addFormInput: function (form, data) {
                var arr = [
                    'type',
                    'toCategoryId',
                    'fromCategoryId',
                    'toCategoryName',
                    'option_1_file_action',
                    'newCategoryId',
                    'newCategoryName'
                ];

                arr.forEach(function (value) {
                    $('<input>', {type: 'hidden', name: value, value: data[value]}).appendTo(form);
                });
            }
        });
        return $.mage.addToWishlist;
    };
});
