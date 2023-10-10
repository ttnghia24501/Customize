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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    "use strict";

    $.widget('mageplaza.transaction', {
        options: {
            url: ''
        },
        isloaded: false,

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initCustomerGrid();
            this.selectCustomer();
        },

        /**
         * Init popup
         * Popup will automatic open
         */
        initPopup: function () {
            var customerId   = $("#customer_id_form").val(),
                options      = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: $t('Select Customer'),
                    buttons: []
                },
                customerGrid = $('#customer-grid');

            modal(options, customerGrid);
            customerGrid.modal('openModal');

            if (customerId) {
                $.each(customerGrid.find('input[name="customer_id"]'), function() {
                    if ($(this).val() === customerId) {
                        $(this).prop('checked', true);
                    }
                });
            }
        },

        /**
         * Init select customer
         */
        selectCustomer: function () {
            $('body').delegate('#customer-grid_table tbody tr', 'click', function () {
                $("#customer_id_form").val($(this).find('input').val().trim());
                $("#customer_email").val($(this).find('td:nth-child(5)').text().trim());
                $('#customer-grid').parent().parent().find('.action-close').trigger('click');
            });
        },

        /**
         * Init customer grid
         */
        initCustomerGrid: function () {
            var self = this;

            $("#customer_email").click(function () {
                $.ajax({
                    method: 'POST',
                    url: self.options.url,
                    data: {form_key: window.FORM_KEY},
                    showLoader: true
                }).done(function (response) {
                    $('#customer-grid').html(response);
                    self.initPopup();
                });
            });
        }
    });

    return $.mageplaza.transaction;
});

