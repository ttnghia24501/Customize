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

/* eslint-disable no-undef */
// jscs:disable jsDoc

define(
    [
        'jquery',
        'mage/template',
        'uiRegistry',
        'mage/translate',
        'jquery/ui',
        'form',
        'validation'
    ], function ($, mageTemplate, rg, $t) {
        'use strict';

        return function (config) {
            var attributeOption = {
                table: $('#attribute-options-table'),
                itemCount: 0,
                totalItems: 0,
                rendered: 0,
                template: mageTemplate('#row-template'),
                isReadOnly: config.isReadOnly,
                add: function (data, render) {
                    var isNewOption = false,
                        d           = new Date(),
                        _id         = d.getTime() + '_' + d.getMilliseconds(),
                        element;

                    if (typeof data.id == 'undefined') {

                        data = {
                            'id': _id,
                            'sort_order': this.itemCount + 1
                        };
                        isNewOption = true;
                    }

                    if (!data.intype) {
                        data.intype = this.getOptionInputType();
                    }

                    element = this.template(
                        {
                            data: data
                        }
                    );

                    if (isNewOption && !this.isReadOnly) {
                        this.enableNewOptionDeleteButton(data.id);
                    }
                    this.itemCount++;
                    this.totalItems++;
                    this.elements += element;

                    if (render) {
                        this.render();
                        this.updateItemsCountField();
                    }
                    $('#manage-options-panel .validation label.mage-error').remove();
                },
                remove: function (event) {
                    var html,
                        element = $(event.target).closest('tr'),
                        elementFlags; // !!! Button already have table parent in safari

                    if ($('[data-role="options-container"]').children('tr').not('.no-display').length === 1) {
                        html = '<label for="groups[general][fields][default_wishlist][value][dropdown_attribute_validation]" ' +
                            'generated="true" ' + 'class="mage-error" ' +
                            'id="groups[general][fields][default_wishlist][value][dropdown_attribute_validation]-error"' +
                            '>' + $t('You need have at least one category') + '</label>';

                        $('#manage-options-panel .validation').append(html);
                        clearInterval(window.checkInterval);
                        window.checkInterval = setInterval(
                            function () {
                                $('#manage-options-panel .validation label.mage-error').remove();
                                clearInterval(window.checkInterval);
                            }, 3000
                        );

                        return;
                    }

                    // Safari workaround
                    element.parents().each(
                        function () {
                            if ($(this).hasClass('option-row')) {
                                element = $(this);
                                throw $break;
                            } else if ($(this).hasClass('box')) {
                                throw $break;
                            }
                        }
                    );

                    if (element) {
                        elementFlags = element.find('.delete-flag');

                        if (elementFlags[0]) {
                            elementFlags[0].value = 1;
                        }

                        element.addClass('no-display');
                        element.addClass('template');
                        element.hide();
                        this.totalItems--;
                        this.updateItemsCountField();
                    }
                },
                updateItemsCountField: function () {
                    $('#option-count-check').val(this.totalItems > 0 ? '1' : '');
                },
                enableNewOptionDeleteButton: function (id) {
                    $('#delete_button_container_' + id + ' button').each(
                        function () {
                            $(this).prop('disabled', false);
                            $(this).removeClass('disabled');
                        }
                    );
                },
                bindRemoveButtons: function () {
                    $('#swatch-visual-options-panel').on('click', '.delete-option', this.remove.bind(this));
                },
                render: function () {
                    $('[data-role=options-container]').append(this.elements);
                    this.elements = '';
                },
                renderWithDelay: function (data, from, step, delay) {
                    var arrayLength = data.length,
                        len;

                    for (len = from + step; from < len && from < arrayLength; from++) {
                        this.add(data[from]);
                    }
                    this.render();

                    if (from === arrayLength) {
                        this.updateItemsCountField();
                        this.rendered = 1;
                        $('body').trigger('processStop');

                        return true;
                    }
                    setTimeout(this.renderWithDelay.bind(this, data, from, step, delay), delay);
                },
                ignoreValidate: function () {
                    $('#config-edit-form').data('validator').settings.forceIgnore = '.ignore-validate input, ' +
                        '.ignore-validate select, ' + '.ignore-validate textarea';
                },
                getOptionInputType: function () {
                    var optionDefaultInputType = 'radio',
                        frontendInputEl        = $('#frontend_input');

                    if (frontendInputEl.length && frontendInputEl.val() === 'multiselect') {
                        optionDefaultInputType = 'checkbox';
                    }

                    return optionDefaultInputType;
                }
            };

            var addNewOptButtonEl = $('#add_new_option_button');

            if (addNewOptButtonEl.length) {
                addNewOptButtonEl.on('click', function () {
                    attributeOption.add({}, true);
                });
            }
            $('#manage-options-panel').on('click', '.delete-option', function (event) {
                attributeOption.remove(event);
            });

            attributeOption.ignoreValidate();

            if (attributeOption.rendered) {
                return false;
            }
            $('body').trigger('processStart');
            attributeOption.renderWithDelay(config.attributesData, 0, 100, 300);
            attributeOption.bindRemoveButtons();

            if (config.isSortable) {

                $('[data-role=options-container]').sortable(
                    {
                        distance: 8,
                        tolerance: 'pointer',
                        cancel: 'input, button',
                        axis: 'y',
                        update: function () {
                            $('[data-role=options-container] [data-role=order]').each(
                                function (index, element) {
                                    $(element).val(index + 1);
                                }
                            );
                        }
                    }
                );
            }

            window.attributeOption = attributeOption;
            window.optionDefaultInputType = attributeOption.getOptionInputType();

            rg.set('manage-options-panel', attributeOption);
        };
    }
);

