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
    'underscore',
    'Mageplaza_ProductFeed/js/lib/codemirror/lib/codemirror',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Mageplaza_ProductFeed/js/lib/codemirror/mode/xml/xml',
    'Mageplaza_ProductFeed/js/lib/codemirror/addon/display/autorefresh',
    'Mageplaza_ProductFeed/js/lib/codemirror/addon/mode/overlay',
    'jquery/ui'
], function ($, _, CodeMirror, modal, $t) {
    "use strict";

    var loadTempBtn    = $('#load-template'),
        fieldsColEl    = $('#fields-map .fields-col'),
        attrSelectHtml = $('#select-attr').html();

    $.widget('mageplaza.initTemplateTab', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initLoadTemplate();
            this.initCodeMirror();
            this.initPopup();
            this.initObservable();
            this.initDraggable();
            this.testDeliveryConnection();
            this.isEncryptor();
            this.initFieldsMap();
        },
        initObservable: function () {
            this.rowSelectObs();
            this.inputObs();
            this.removeModifierObs();
            this.addModifierObs();
            this.initChangeFileType();
        },

        initChangeFileType: function () {
            var self            = this,
                fileType        = $('#feed_file_type'),
                defaultTemplate = $("#feed_default_template");

            var updateDefaultTemplate = function () {
                var hasDefaultTemplate = false;

                defaultTemplate.children().remove();
                $.each(self.options.defaultTemplate, function (key, template) {
                    if (fileType.val() === template.type) {
                        defaultTemplate.append($("<option></option>")
                        .attr("value", key)
                        .text(template.label));

                        hasDefaultTemplate = true;
                    }
                });

                if (!hasDefaultTemplate) {
                    defaultTemplate.parents('.field-default_template').hide();
                } else {
                    defaultTemplate.parents('.field-default_template').show();
                }
            };

            fileType.on('change', function () {
                updateDefaultTemplate();
            });
            updateDefaultTemplate();
        },

        initPopup: function () {
            var options = {
                type: 'slide',
                responsive: true,
                innerScroll: true,
                title: $t('Insert Variable'),
                subTitle: $t('Click to each attribute to add filter & insert it to template'),
                buttons: []
            };

            modal(options, $('#insert-variable-popup'));

            $('a#insert-variable').click(function () {
                $('#insert-variable-popup').modal('openModal');
            });
        },
        initDraggable: function () {
            var self = this;

            $('#insert-variable-popup .modifier-group').sortable({
                stop: function () {
                    var attr_code = $(this).attr('code');

                    self.updateVariable(attr_code);
                }
            });
        },
        initLoadTemplate: function () {
            var self = this;

            loadTempBtn.click(function () {
                loadTempBtn.text($t('Loading...')).addClass('loading');
                $.ajax({
                    url: self.options.url,
                    data: {name: $('#feed_default_template').val()},
                    type: 'POST',
                    success: function (res) {
                        $('#feed_file_type').val(res.file_type);
                        $('#feed_field_separate').val(res.field_separate);
                        $('#feed_field_around').val(res.field_around);
                        $('#feed_include_header').val(res.include_header);
                        if (res.template_html && res.template_html !== '') {
                            $('#feed_template_html').val(res.template_html);
                            self.options.doc.setValue(res.template_html);
                        }
                        if (res.fields_map && res.fields_map !== '') {
                            $('#fields-map .fields-col').html('');
                            self.renderFieldsMap(JSON.parse(res.fields_map));
                        }
                        document.getElementById('feed_file_type').dispatchEvent(new Event('change'));
                        $('#feed_default_template').val(res.name);
                        loadTempBtn.text($t('Load Template')).removeClass('loading');
                    }
                });
            });
        },
        initCodeMirror: function () {
            var self = this;

            this.options.codeMirror = CodeMirror.fromTextArea(document.getElementById("feed_template_html"), {
                mode: 'xml',
                lineNumbers: true,
                autofocus: true,
                autoRefresh: true,
                styleActiveLine: true,
                viewportMargin: Infinity
            });
            this.options.codeMirror.addOverlay({
                token: function (stream) {
                    var query = /^{{.*?}}/g;

                    if (stream.match(query)) {
                        return 'liquid-variable';
                    }
                    stream.next();
                }
            });
            this.options.codeMirror.addOverlay({
                token: function (stream) {
                    var query = /^{%.*?%}/g;

                    if (stream.match(query)) {
                        return 'liquid-method';
                    }
                    stream.next();
                }
            });
            this.options.codeMirror.on('change', function (cMirror) {
                $("#feed_template_html").val(cMirror.getValue());
            });
            this.options.doc = this.options.codeMirror.getDoc();
            $('.insert').on('click', function () {
                var cursor = self.options.doc.getCursor();

                self.options.doc.replaceRange($(this).siblings('.liquid-variable').text(), cursor);
                $('[data-role="closeBtn"].action-close').trigger('click');
            });
        },
        testDeliveryConnection: function () {
            var testBtn = $('#feed_test_connect'),
                mesEl   = $('.test-connect-message'),
                self    = this,
                pass    = self.options.password;

            testBtn.click(function () {
                var protocol = $('#feed_protocol').val(),
                    host     = $('#feed_host_name').val(),
                    user     = $('#feed_user_name').val(),
                    passive  = $('#feed_passive_mode').val(),
                    path     = $('#feed_path_key').val();

                if ($('#feed_is_encryptor').val() === '0') {
                    pass = $('#feed_password').val();
                }

                testBtn.attr('disabled', true);
                testBtn.val($t('Testing...'));
                $.ajax({
                    url: self.options.testConnectionUrl,
                    type: 'POST',
                    data: {protocol: protocol, host: host, passive: passive, user: user, pass: pass, path: path},
                    success: function (res) {
                        if (res === 1) {
                            mesEl.html('<p style="color:green;margin-left: 20px">' + $t('Connection Success') + '</p>');
                        } else {
                            mesEl.html('<p style="color:red;margin-left: 20px">' + $t('Connection Fail') + '</p>');
                        }
                    },
                    complete: function () {
                        testBtn.attr('disabled', false);
                        testBtn.val($t('Test Connection'));
                    }
                });
            });
        },

        isEncryptor: function () {
            var passEl        = $('#feed_password'),
                isEncryptorEl = $('#feed_is_encryptor');

            passEl.on('change', function () {
                isEncryptorEl.val('0');
            });
        },

        rowSelectObs: function () {
            var self = this;

            $('#insert-variable-popup').on('change', 'select', function () {
                var elf       = $(this),
                    paramsEl  = elf.siblings('.params'),
                    attr_code = elf.parents('.modifier').attr('code');

                paramsEl.html('');
                if (elf.val() !== 0) {
                    _.each(self.options.modifiersData[this.value].params, function (record) {
                        paramsEl.append('<span class="modifier-param">' + record.label
                            + '</span><input class="modifier-param" type="text" code="' + attr_code + '">');
                    });
                }
                self.updateVariable(attr_code);
            });
        },
        inputObs: function () {
            var self = this;

            $('#insert-variable-popup').on('change', 'input', function () {
                var attr_code = $(this).attr('code');

                self.updateVariable(attr_code);
            });
        },
        removeModifierObs: function () {
            var self = this;

            $('#insert-variable-popup').on('click', '.remove-modifier', function () {
                var rowModifier = $(this).parent().parent().parent();
                var attr_code   = $(this).parents('.modifier').attr('code');

                $(this).parent().parent().remove();
                self.updateVariable(attr_code);

                if (!rowModifier.children().length) {
                    rowModifier.parent().removeClass('show');
                }
            });
        },
        addModifierObs: function () {
            var self = this;

            $('#insert-variable-popup').on('click', '.add-modifier', function () {
                var rowModifier = $(this).parent().siblings('.row-modifier'),
                    opt         = '', modifierEl,
                    attr_code   = $(this).parents('.attr-code').attr('code');

                if (!rowModifier.hasClass('show')) {
                    rowModifier.addClass('show');
                }

                _.each(self.options.modifiersData, function (record, index) {
                    opt += '<option value="' + index + '">' + record.label + '</option>';
                });
                modifierEl =
                    '<div class="modifier" code="' + attr_code + '"><div class="row"><select><option value="0">'
                    + $t('--Please Select--') + '</option>' +
                    opt +
                    '</select><div class="params"></div><button title="' + $t('Delete')
                    + '" type="button" class="action- scalable delete delete-option remove-modifier"><span>'
                    + $t('Delete') + '</span></button></div></div>';
                $(this).parent().parent().find('.modifier-group').append(modifierEl);
            });
        },
        updateVariable: function (attr_code) {
            var parentEl = $('[code="' + attr_code + '"]'),
                str      = '{{ ';

            str += 'product.' + attr_code;
            parentEl.find('.modifier').each(function () {
                var modifier = $(this).find('select').val(),
                    params   = $(this).find('input.modifier-param');

                if (modifier && modifier !== '0') {
                    str += ' | ' + modifier;
                }
                if (params.length) {
                    str += ': ';

                    params.each(function (index) {
                        if (index === params.length - 1) {
                            str += "'" + this.value + "'";
                            return;
                        }
                        str += "'" + this.value + "', ";
                    });
                }
            });
            str += ' }}';
            parentEl.find('.liquid-variable').text(str);
        },
        initFieldsMap: function () {
            this.modifierCollapse();
            this.changeValObs();
            this.removeFieldsMapModifierObs();
            this.selectModifierObs();
            this.addFieldsMapModifierObs();
            this.removeRowObs();
            this.selectTypeObs();
            this.addRowObs();
            this.initFieldsMapDraggable();
            this.renderFieldsMap(this.options.fieldsMap);
        },
        modifierCollapse: function () {
            var self = this;

            $('#fields-map').on('click', 'a.modifier-collapse', function () {
                var i = $(this).find('i');

                $(this).parents('.field-col').find('.modifier-group').toggle();
                self.collapse(i);
            });
        },
        changeValObs: function () {
            var self = this;

            $('#fields-map').on('change', '.col-value input,.modifier-group input,.col-value select', function () {
                var attrEl = $(this).parents('.field-col');

                self.updateFieldMapVariable(attrEl);
            });
        },
        removeFieldsMapModifierObs: function () {
            var self = this;

            $('#fields-map').on('click', 'a.remove-modifier', function () {
                var attrEl = $(this).parents('.field-col');

                $(this).parent().remove();
                self.updateFieldMapVariable(attrEl);
            });
        },
        selectModifierObs: function () {
            var self = this;

            $('#fields-map').on('change', '.modifier select', function () {
                var modifierId = $(this).parents('.modifier').attr('id'),
                    elf        = $(this),
                    paramsEl   = elf.siblings('.params'),
                    attrEl     = $('#' + modifierId).parents('.field-col');

                paramsEl.html('');
                if (elf.val() !== 0) {
                    self.createModifierParams(modifierId);
                }
                self.updateFieldMapVariable(attrEl);
            });
        },
        addFieldsMapModifierObs: function () {
            var self = this;

            $('#fields-map').on('click', 'a.add-modifier', function () {
                var i     = $(this).parents('.field-col').find('.col-collapsible i'),
                    rowId = this.id,
                    d     = new Date(),
                    _id   = d.getTime() + '_' + d.getMilliseconds(),
                    modifierGroupEl;

                // eslint-disable-next-line eqeqeq
                if ($(this).parents('.field-col').find('.col-type select').val() === '0') {
                    return;
                }
                self.createModifierRow(rowId, _id);

                modifierGroupEl = $('#' + rowId).find('.modifier-group');
                modifierGroupEl.show();
                if (i.hasClass('fa-chevron-down')) {
                    i.removeClass('fa-chevron-down');
                    i.addClass('fa-chevron-up');
                }
            });
        },
        removeRowObs: function () {
            $('#fields-map').on('click', 'a.col-remove', function () {
                $(this).parents('.field-col').remove();
            });
        },
        selectTypeObs: function () {
            $('#fields-map').on('change', '.col-type select', function () {
                var typeEl = $(this),
                    valEl  = typeEl.parent().siblings('.col-value');

                if (typeEl.val() === 'attribute') {
                    typeEl.parent().siblings('.col-add-modifier').show();
                    typeEl.parent().siblings('.col-collapsible').css('visibility', 'visible');
                    valEl.find('input').hide();
                    valEl.find('select').show();
                } else {
                    typeEl.parent().siblings('.col-add-modifier').hide();
                    typeEl.parent().siblings('.col-collapsible').css('visibility', 'hidden');

                    valEl.find('input').show();
                    valEl.find('select').hide();
                }
            });
        },
        addRowObs: function () {
            var self = this;

            $('#add-column').click(function () {
                var d   = new Date(),
                    _id = d.getTime() + '_' + d.getMilliseconds();

                self.createRow(_id);
            });
        },
        initFieldsMapDraggable: function () {
            var self = this;

            $('.fields-col').sortable();
            $('#fields-map .modifier-group').sortable({
                stop: function () {
                    var attrEl = $(this).parents('.field-col');

                    self.updateFieldMapVariable(attrEl);
                }
            });
        },
        createRow: function (_id) {
            var $html =
                    '<div class="field-col row" id="' + _id + '">' +
                    '    <div class="col-row row">' +
                    '    <div class="col-drag">\n' +
                    '    </div>\n' +
                    '    <div class="col-name">\n' +
                    '        <input type="text" name="feed[fields_map][' + _id + '][col_name]">\n' +
                    '    </div>\n' +
                    '    <div class="col-type">\n' +
                    '        <select name="feed[fields_map][' + _id + '][col_type]">\n' +
                    '            <option value="attribute">' + $t('Attribute') + '</option>\n' +
                    '            <option value="pattern">' + $t('Pattern') + '</option>\n' +
                    '        </select>\n' +
                    '    </div>\n' +
                    '    <div class="col-value">\n' +
                    '        <select name="feed[fields_map][' + _id + '][col_attr_val]">' + attrSelectHtml +
                    '        </select>' +
                    '        <input name="feed[fields_map][' + _id + '][col_pattern_val]" ' +
                    '           type="text" class="pattern" style="display: none">\n' +
                    '        <input name="feed[fields_map][' + _id + '][col_val]" ' +
                    '           type="hidden" class="liquid-variable">\n' +
                    '    </div>\n' +
                    '    <div class="col-collapsible">\n' +
                    '        <a class="modifier-collapse"><i class="fa fa-chevron-down"></i></a>' +
                    '    </div>\n' +
                    '    <div class="col-remove">' +
                    '        <a class="col-remove btn">' + $t('Remove') + '</a>' +
                    '    </div>\n' +
                    '    <div class="col-add-modifier">' +
                    '        <a class="col-add-modifier add-modifier btn" id="' + _id + '">'
                    + $t('Add Filter') + '</a>' +
                    '    </div>' +
                    '    </div>' +
                    '    <div class ="modifier-group"></div>' +
                    '</div>';

            fieldsColEl.append($html);
        },
        createModifierParams: function (modifierId, params) {
            var self         = this,
                modifierName = $('#' + modifierId).attr('name'),
                paramsEl     = $('#' + modifierId + ' .params'),
                attr_code    = $('#' + modifierId + ' select').val();

            params = params || {};

            if (attr_code === 0) {
                return;
            }

            _.each(self.options.modifiersData[attr_code].params, function (record, index) {
                paramsEl.append('<span class="modifier-param">' + record.label + '</span><input value="'
                    + (params[index] === undefined ? '' : params[index]) +
                    '" name="' + modifierName + '[params][' + index + ']" class="modifier-param" type="text">');
            });
        },
        createModifierRow: function (rowId, _id) {
            var self = this,
                opt  = '', modifierEl, modifierGroupEl;

            _.each(self.options.modifiersData, function (record, index) {
                opt += '<option value="' + index + '">' + record.label + '</option>';
            });

            modifierEl      = '<div class="modifier" id="' + _id + '" name="feed[fields_map]['
                + rowId + '][modifiers][' + _id + ']"><div class="row">' +
                '<select name="feed[fields_map][' + rowId + '][modifiers]['
                + _id + '][value]" id="feed[fields_map][' + rowId + '][modifiers]['
                + _id + ']" style="float: left; width: 200px; margin-right: 50px"><option value="0">'
                + $t('--Please Select--') + '</option>' +
                opt +
                '</select><div class="params" style="float: left;"></div><a class="remove-modifier btn">'
                + $t('Remove') + '</a></div></div>';
            modifierGroupEl = $('#' + rowId).find('.modifier-group');

            modifierGroupEl.append(modifierEl);
        },
        collapse: function (i) {
            if (i.hasClass('fa-chevron-down')) {
                i.removeClass('fa-chevron-down');
                i.addClass('fa-chevron-up');
            } else {
                i.removeClass('fa-chevron-up');
                i.addClass('fa-chevron-down');
            }
        },
        updateFieldMapVariable: function (attrEl) {
            var self      = this,
                attr_code = attrEl.find('.col-value select').val(),
                str       = '';

            if (attr_code && attrEl.find('.col-type select').val() === 'attribute') {
                str = '{{ ';
                str += 'product.' + attr_code;
                attrEl.find('.modifier').each(function () {
                    var modifier = $(this).find('select').val(),
                        params   = $(this).find('input.modifier-param');

                    if (modifier === "0") {
                        return;
                    }
                    str += ' | ' + modifier;
                    if (params.length) {
                        str += ': ';

                        params.each(function (index) {
                            if (index === params.length - 1) {
                                str += "'" + self.htmlEntities(this.value) + "'";
                                return;
                            }
                            str += "'" + self.htmlEntities(this.value) + "', ";
                        });
                    }
                });
                str += ' }}';
            }
            attrEl.find('input.liquid-variable').val(str);
        },
        htmlEntities: function (str) {
            return String(str).replace(/"/g, '&quot;').replace(/'/g, '&apos;');
        },
        renderFieldsMap: function (fieldsMap) {
            var self = this;

            _.each(fieldsMap, function (record, index) {
                if (record.col_type === 'attribute' && record.col_attr_val === 0
                    || record.col_type === 'pattern' && record.col_pattern_val === ''
                ) {
                    return;
                }
                self.createRow(index);
                $('#' + index + ' .col-name input').val(record.col_name);
                $('#' + index + ' .col-value select').val(record.col_attr_val);
                $('#' + index + ' .col-value .pattern').val(record.col_pattern_val);
                $('#' + index + ' .col-value .liquid-variable').val(record.col_val);
                $('#' + index + ' .col-type select').val(record.col_type).trigger('change');

                _.each(record.modifiers, function (modifier, key) {
                    if (modifier.value === 0) {
                        return;
                    }
                    self.createModifierRow(index, key);
                    $('#' + key + ' select').val(modifier.value);
                    if (modifier.params !== undefined) {
                        self.createModifierParams(key, modifier.params);
                    }
                });
                self.updateFieldMapVariable($('#' + index));
            });
        }
    });

    return $.mageplaza.initTemplateTab;
});
