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
    'Magento_Ui/js/modal/modal'
], function ($, $t) {
    'use strict';

    window.FeedVariables = {
        variablesContent: null,
        dialogWindowId: 'variables-chooser',
        editor: null,

        /**
         * Set editor
         * @param editor
         */
        setEditor: function (editor) {
            this.editor = editor;
        },

        /**
         * Open variable chooser
         * @param variables
         */
        openVariableChooser: function (variables) {
            variables = [variables];
            if (this.variablesContent === null && variables) {
                this.variablesContent = '';
                variables.each(function (variableGroup) {
                    variableGroup.each(function (group) {
                        if (group.code === 'other') {
                            this.variablesContent += '<ul class="insert-variable-' + group.code + '" style="list-style: none; float: left; padding: 20px; column-count: 5">';
                        } else {
                            this.variablesContent += '<ul class="insert-variable-' + group.code + '" style="list-style: none; float: left; padding: 20px">';
                        }
                        if (group.label && group.value) {
                            this.variablesContent += '<li><b>' + group.label + '</b></li>';
                            group.value.each(function (variable) {
                                if (variable.value && variable.label) {
                                    this.variablesContent += '<li>' +
                                        this.prepareVariableRow(variable.value, variable.label) + '</li>';
                                }
                            }.bind(this));
                            this.variablesContent += '</ul>';
                        }
                    }.bind(this))
                }.bind(this));
            }

            if (this.variablesContent) {
                this.openDialogWindow(this.variablesContent);
            }
        },

        /**
         * Open popup
         * @param variablesContent
         */
        openDialogWindow: function (variablesContent) {
            var windowId = this.dialogWindowId;

            $('<div id="' + windowId + '">' + variablesContent + '</div>').modal({
                title: $t('Insert Variable...'),
                type: 'slide',
                buttons: [],
                closed: function (e, modal) {
                    modal.modal.remove();
                }
            });

            $('#' + windowId).modal('openModal');
        },

        /**
         * Close popup
         */
        closeDialogWindow: function () {
            $('#' + this.dialogWindowId).modal('closeModal');
        },

        /**
         * Prepare variable row
         * @param varValue
         * @param varLabel
         * @returns {string}
         */
        prepareVariableRow: function (varValue, varLabel) {
            return '<span class="mp-feed-on-click" onclick="' + this.insertFunction(varValue) + '">' + varLabel + '</span>';
        },

        /**
         * @param value
         * @returns {string}
         */
        insertFunction: function (value) {
            return 'FeedVariables.insertVariable(&apos;' + value + '&apos;)';
        },

        /**
         * Insert variable to editor at cursor
         * @param value
         */
        insertVariable: function (value) {
            var editorElement = $('#' + this.editor)[0],
                scrollPos     = editorElement.scrollTop;

            /* global updateElementAtCursor */
            updateElementAtCursor(editorElement, value);
            editorElement.focus();
            editorElement.scrollTop = scrollPos;
            $(editorElement).change();

            $('#' + this.dialogWindowId).modal('closeModal');
        }
    };

    return window.FeedVariables;
});
