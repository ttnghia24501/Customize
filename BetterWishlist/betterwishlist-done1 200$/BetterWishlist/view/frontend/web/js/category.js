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
        'mage/dataPost',
        'Magento_Customer/js/section-config',
        'Magento_Customer/js/customer-data',
    ], function ($, _, modal, $t, dataPost, sectionConfig, customerData) {
        'use strict';

        var categoryTableEl  = $('.admin__action-dropdown-wrap.admin__data-grid-action-bookmarks.multiple-view'),
            bodyEl           = $('body'),
            errorMesEl       = $('.mp-wishlist-category .error-messages'),
            productGridEl    = $('.products-table.wishlist'),
            controlButtonsEl = $('#wishlist-view-form > .actions-toolbar'),
            toolbarEl        = $('.toolbar.wishlist-toolbar'),
            selectCatModal   = $('#category-select-modal'),
            toNewWishlistEl  = $('#category-select-modal .to-new-wishlist'),
            actionTypeEl     = $('#category-select-modal #action-type'),
            newCatInput      = $('#category-select-modal #new-category'),
            listCat          = $('ul.admin__action-dropdown-menu');

        $.widget(
            'mageplaza.wishlistCategory', {
                options: {
                    nameArray: {},
                    addToCartUrl: '',
                    updateCatUrl: '',
                    wishlistUrl: '',
                    shareWishlistUrl: '',
                    updateWishlistUrl: '',
                    limitWishlist: 5,
                    isMultiple: true,
                    isAllowCustomerCreate: false,
                    form_key: $('[name="form_key"]').val(),
                    activeCatId: '',
                    isRemove: false
                },
                _create: function () {
                    if (this.getActiveCatId() === 'all') {
                        $('body').addClass('all-wishlist-item');
                    }
                    customerData.reload(['customer', 'cart', 'wishlist'], false);
                    this.init();

                    if (this.options.activeCatId !== '') {
                        this.changeActiveCat('li#' + this.options.activeCatId + ' .action-dropdown-menu-link');
                    }
                },
                init: function () {
                    this.toolbarObs ();
                    this.initNameArray();
                    this.editCatObs();
                    this.resetCatObs();
                    this.loadCatObs();
                    this.addNewCat();
                    this.saveCatObs();
                    this.editCatEnterObs();
                    this.deleteCatObs();
                    this.changeCatObs();
                    this.copyProductObs();
                    this.moveProductObs();
                    this.deleteProduct();
                    this.toNewWishlistObs();
                    this.moveCopySubmitObs();
                    this.editProductObs();
                    this.shareWishlistObs();
                    this.shareWishlistSubmit();
                    this.updateWishlistObs();
                    this.addToCartObs();
                    this.addAllToCartObs();
                    this.reindexToolbar();
                },
                reindexToolbar: function () {
                    var self = this,
                        data = {type: 'reindex_toolbar'};
                    $.ajax({
                        url: self.options.wishlistUrl,
                        method: 'POST',
                        data: data,
                        showLoader: true,
                        success: function (res) {
                            self.resetToolbar(res);
                        }
                    });
                },
                getActiveCatId: function () {
                    return $('#mp-wishlist-category-id').val();
                },
                initNameArray: function () {
                    var self = this;

                    categoryTableEl.find('li').each(
                        function () {
                            self.options.nameArray[$(this).attr('id')]
                                = $(this).find('.action-dropdown-menu-link span').text().trim();
                        }
                    );
                },
                editCatObs: function () {
                    categoryTableEl.on('click', '.action-dropdown-menu-item .action-edit', function () {
                            var parentEl = $(this).parents('li');

                            $('.li-action-dropdown-menu-item.undefined').remove();
                            parentEl.addClass('_edit');
                            parentEl.siblings().removeClass('_edit');
                        }
                    );
                },
                addToCartObs: function () {
                    var self = this;

                    bodyEl.on('click', 'button.action.tocart.primary', function (e) {
                            var url,
                                defaultUrl,
                                data;

                            e.preventDefault();
                            e.stopPropagation();
                            url                 = self.options.addToCartUrl;
                            defaultUrl          = self.options.addToCartUrlDefault;
                            data                = $(this).data('post').data;
                            data.fromCategoryId = self.getActiveCatId();
                            url += '?form_key=' + self.options.form_key;
                            $.ajax({
                                url: url,
                                method: 'POST',
                                data: data,
                                showLoader: true,
                                success: function (res) {
                                    if (res.backUrl) {
                                        dataPost().postData({action: defaultUrl, data: data});
                                    } else {
                                        sectionConfig.getAffectedSections(url);
                                        customerData.reload(['customer', 'cart', 'wishlist'], false);
                                        if (self.options.isRemove) {
                                            self.resetProductGrid(res);
                                        }
                                    }
                                },
                                error: function () {
                                    self.reloadPage();
                                }
                            });
                        }
                    );
                },
                resetCatObs: function () {
                    bodyEl.on('click', function (e) {
                            if (!$(e.target).parents().hasClass('mp-wishlist-category')) {
                                categoryTableEl.find('li').removeClass('_edit');
                                categoryTableEl.find('.undefined').remove();
                            }
                        }
                    );
                },
                showErrorMes: function (mes) {
                    errorMesEl.html('<label class="admin__field-error">' + mes + '</label>');
                    clearInterval(window.mpCheckInterval);
                    window.mpCheckInterval = setInterval(
                        function () {
                            errorMesEl.html('');
                            clearInterval(window.mpCheckInterval);
                        }, 3000
                    );
                },
                saveCatObs: function () {
                    var self = this;

                    categoryTableEl.on('click', '.li-action-dropdown-menu-item .action-submit', function () {
                            var parentEl = $(this).parents('li'),
                                inputEl  = parentEl.find('input'),
                                labelEl  = parentEl.find('.action-dropdown-menu-link span'),
                                oldLabel = labelEl.text();

                            if (_.values(self.options.nameArray).indexOf(inputEl.val()) > -1
                                && inputEl.val() !== labelEl.text()) {
                                if (errorMesEl.children().length) {
                                    return;
                                }
                                self.showErrorMes($t('Name must be unique and not null'));
                                return;
                            } else if (inputEl.val().trim() === '') {
                                if (errorMesEl.children().length) {
                                    return;
                                }
                                self.showErrorMes($t('Name must be unique and not null'));
                                return;
                            }
                            self.options.nameArray[parentEl.attr('id')] = inputEl.val();
                            labelEl.text(inputEl.val());
                            inputEl.attr('placeholder', inputEl.val());
                            parentEl.removeClass('_edit');
                            parentEl.removeClass('undefined');
                            parentEl.addClass('waiting-save');

                            $.ajax(
                                {
                                    url: self.options.updateCatUrl,
                                    method: 'POST',
                                    data: {categoryId: parentEl.attr('id'), categoryName: inputEl.val()},
                                    success: function (res) {
                                        if (res.error) {
                                            $('li.waiting-save').remove();
                                            labelEl.text(oldLabel);
                                            return;
                                        }
                                        parentEl.removeClass('waiting-save');
                                    },
                                    error: function () {
                                        $('li.waiting-save').remove();
                                        labelEl.text(oldLabel);
                                    }
                                }
                            );
                        }
                    );
                },
                deleteCatObs: function () {
                    var elf = this;

                    categoryTableEl.on('click', '.action-delete', function () {
                            var parentEl    = $(this).parents('li'),
                                activeCatId = elf.getActiveCatId(),
                                categoryId  = parentEl.attr('id'),
                                self;

                            $(this).parents('li').hide();
                            if (activeCatId === categoryId) {
                                $('.action-dropdown-menu-link.default-category').trigger('click');
                            }

                            self = this;
                            $.ajax(
                                {
                                    url: elf.options.updateCatUrl,
                                    method: 'POST',
                                    data: {categoryId: categoryId, delete: true},
                                    success: function (res) {
                                        if (res.error) {
                                            $(self).parents('li').show();
                                            return;
                                        }
                                        delete elf.options.nameArray[parentEl.attr('id')];
                                        $(self).parents('li').remove();
                                    },
                                    error: function () {
                                        $(self).parents('li').show();
                                    }
                                }
                            );
                        }
                    );
                },
                resetProductGrid: function (res) {
                    var productGrid = res.productGrid || false;

                    if (productGrid) {
                        productGridEl.html(productGrid);
                        productGridEl.trigger('contentUpdated');
                        this.resetControlButtons(res);
                        this.resetToolbar(res);
                    } else {
                        this.reloadPage();
                    }
                },
                resetToolbar: function (res) {
                    var toolbar = res.toolbar || '';

                    toolbarEl.html(toolbar);
                    toolbarEl.trigger('contentUpdated');
                },
                resetControlButtons: function (res) {
                    var buttonWishlist = '',
                        controlButtons = res.controlButtons || false;

                    if (controlButtons) {
                        if (controlButtons.toCart) {
                            buttonWishlist = controlButtons.update + controlButtons.share + controlButtons.toCart;
                        } else {
                            buttonWishlist = controlButtons.update + controlButtons.share;
                        }
                        controlButtonsEl.find('.primary').html(buttonWishlist);
                        controlButtonsEl.trigger('contentUpdated');
                    } else {
                        this.reloadPage();
                    }
                },
                reloadPage: function () {
                    window.location.reload();
                },
                changeActiveCat: function (self) {
                    var parentEl = $(self).parents('li'),
                        viewId   = parentEl.attr('id');

                    categoryTableEl.find('li').removeClass('primary');
                    categoryTableEl.find('.action-dropdown-menu-link').removeClass('primary');
                    parentEl.addClass('primary');
                    parentEl.find('.action-dropdown-menu-link').addClass('primary');
                    $('#mp-wishlist-category-id').val(viewId);

                    if (parentEl.hasClass('un-remove-able')) {
                        $('body').addClass('un-remove-able');
                    } else {
                        $('body').removeClass('un-remove-able');
                    }
                    if (viewId === 'all') {
                        $('body').addClass('all-wishlist-item');
                    } else {
                        $('body').removeClass('all-wishlist-item');
                    }
                },
                changeCatObs: function () {
                    var self = this;

                    categoryTableEl.on('click', '.action-dropdown-menu-link', function (e) {
                            var parentEl = $(this).parents('li'),
                                viewId   = parentEl.attr('id');

                            e.stopPropagation();
                            e.preventDefault();
                            self.changeActiveCat(this);
                            self.ajaxLoadWishlist(self.options.wishlistUrl,viewId);
                        }
                    );
                },
                toolbarObs: function(){
                    var self = this;

                    bodyEl.on('click', '.toolbar.wishlist-toolbar .pages .pages-items a', function (e) {
                            e.stopPropagation();
                            e.preventDefault();
                            self.ajaxLoadWishlist($(this).attr('href'),self.getActiveCatId());
                        }
                    );
                    bodyEl.on('change', '.toolbar.wishlist-toolbar .limiter select', function (e) {
                            e.stopPropagation();
                            e.preventDefault();
                            self.ajaxLoadWishlist($(this).val(),self.getActiveCatId());
                        }
                    );
                },
                ajaxLoadWishlist: function(url,categoryId) {
                    var self = this;

                    $.ajax(
                        {
                            url: url,
                            method: 'POST',
                            showLoader: true,
                            data: {
                                type: 'load',
                                fromCategoryId: categoryId
                            },
                            success: function (res) {
                                self.resetProductGrid(res);
                                $('.page.messages .messages').html('');
                            },
                            error: function () {
                                self.reloadPage();
                            }
                        }
                    );
                },
                getCatHtml: function (categoryId, label) {
                    var classText = 'li-action-dropdown-menu-item';

                    if (label === '') {
                        classText += ' _edit undefined';
                    }
                    return '   <li class="' + classText + '" id="' + categoryId + '">' +
                        '       <div class = "action-dropdown-menu-item-edit">' +
                        '           <input class="admin__control-text" type="text" value="'
                        + label + '" placeholder="' + label + '">' +
                        '           <button class="action-submit" type="button" title="Save">' +
                        '               <span>Submit</span>' +
                        '           </button>' +
                        '           <div class="action-dropdown-menu-item-actions">' +
                        '               <button class="action-delete" type="button" title="Delete">' +
                        '                   <span>Delete</span>' +
                        '               </button>' +
                        '           </div>' +
                        '       </div>' +
                        '       <div class="action-dropdown-menu-item">' +
                        '           <button class="action-dropdown-menu-link">' +
                        '               <span>' + label + '</span>' +
                        '           </button>' +
                        '           <div class="action-dropdown-menu-item-actions">' +
                        '               <button class="action-edit" type="button" title="Edit">' +
                        '                   <span>Edit</span>' +
                        '               </button>' +
                        '           </div>' +
                        '       </div>' +
                        '   </li>';
                },
                addNewCat: function () {
                    var self = this;

                    categoryTableEl.on('click', '#save-view-as', function (e) {
                            var editableCatEl = $('.mp-wishlist-category li').not('.un-remove-able'),
                                date          = new Date(),
                                categoryId,
                                viewHtml;

                            e.stopPropagation();
                            e.preventDefault();
                            if (editableCatEl.length >= self.options.limitWishlist) {
                                if (errorMesEl.children().length) {
                                    return;
                                }
                                self.showErrorMes($t('Wishlist number limit has been reached.'));
                                return;
                            }
                            categoryId = date.getTime() + '_' + date.getMilliseconds();
                            viewHtml = self.getCatHtml(categoryId, '');
                            listCat.append(viewHtml);
                        }
                    );
                },
                mcProduct: function (type, self) {
                    var title,
                        options;

                    title = type === 'move' ? $t('Move Item to Wishlist') : $t('Copy Item to Wishlist');
                    options = {
                        'type': 'popup',
                        'title': title,
                        'responsive': true,
                        'innerScroll': true,
                        'buttons': []
                    };
                    $('#category-select-modal #item-id').val($(self).attr('data-item-id'));
                    actionTypeEl.val(type);
                    selectCatModal.trigger('loadCategory');
                    modal(options, selectCatModal).openModal();
                },
                copyProductObs: function () {
                    var self = this;

                    bodyEl.on('click', '.mp-wishlist-copy', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            self.mcProduct('copy', this);
                        }
                    );
                },
                moveProductObs: function () {
                    var self = this;

                    bodyEl.on('click', '.mp-wishlist-move', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            self.mcProduct('move', this);
                        }
                    );
                },
                deleteProduct: function () {
                    var self = this;

                    bodyEl.on('click', '.mp-wishlist-delete', function (e) {
                            var fromCategoryId   = self.getActiveCatId(),
                                fromCategoryName = $('.mp-wishlist-category li.primary .action-dropdown-menu-link span')
                                    .text();

                            e.preventDefault();
                            e.stopPropagation();
                            $.ajax(
                                {
                                    url: self.options.wishlistUrl,
                                    method: 'POST',
                                    showLoader: true,
                                    data: {
                                        type: 'delete',
                                        itemId: $(this).attr('data-item-id'),
                                        fromCategoryId: fromCategoryId,
                                        fromCategoryName: fromCategoryName
                                    },
                                    success: function (res) {
                                        customerData.reload(['customer', 'wishlist'], false);
                                        self.resetProductGrid(res);
                                    },
                                    error: function () {
                                        self.reloadPage();
                                    }
                                }
                            );
                        }
                    );
                },
                loadCatObs: function () {
                    var self = this;

                    selectCatModal.on('loadCategory', function () {
                            var html = '',
                                editableCatEl;

                            if (self.options.isMultiple) {
                                editableCatEl = $('.mp-wishlist-category li').not('.un-remove-able');

                                _.each(
                                    self.options.nameArray, function (item, index) {
                                        var selected;

                                        if ($('.mp-wishlist-category li.primary').attr('id') === index
                                            || index === 'all') {
                                            return;
                                        }
                                        selected = index === '<?= $defaultCategory->getId() ?>' ? ' selected' : '';
                                        html += '<option value="' + index + '"' + selected + '>' + item + '</option>';
                                    }
                                );
                                if (self.options.isAllowCustomerCreate
                                    && editableCatEl.length < self.options.limitWishlist) {
                                    html += '<option value="new">' + $t('New Wish List') + '</option>';
                                }
                            } else {
                                html += '<option value="all" selected>All</option>';
                            }

                            $('#category-select-modal select').html(html);
                            toNewWishlistEl.hide();
                            newCatInput.val('');
                            selectCatModal.find('select').trigger('change');
                        }
                    );
                },
                toNewWishlistObs: function () {
                    bodyEl.on('change', '#category-select-modal select', function () {
                            if ($(this).val() === 'new') {
                                toNewWishlistEl.show();
                            } else {
                                toNewWishlistEl.hide();
                            }
                        }
                    );
                },
                moveCopySubmitObs: function () {
                    var self = this;

                    bodyEl.on('click', '#category-select-modal #mc-to-wishlist', function () {
                            var itemId           = $('#category-select-modal #item-id').val(),
                                toCategoryId     = $('#category-select-modal select').val(),
                                fromCategoryId   = self.getActiveCatId(),
                                fromCategoryName = $('li.primary .action-dropdown-menu-link span').text(),
                                toCategoryName   = $('#category-select-modal select :selected').text(),
                                type             = actionTypeEl.val(),
                                newCategoryId    = '',
                                viewLabel        = '',
                                date,
                                viewHtml;

                            if (toCategoryId === 'new') {
                                date      = new Date();
                                viewLabel = newCatInput.val().trim();
                                if (viewLabel === '') {
                                    toNewWishlistEl.find('.error-messages').html('<label class="admin__field-error">'
                                        + $t('Please fill in the wishlist name. ') + '</label>');
                                    clearInterval(window.mpCheckInterval);
                                    window.mpCheckInterval = setInterval(
                                        function () {
                                            toNewWishlistEl.find('.admin__field-error').remove();
                                            clearInterval(window.mpCheckInterval);
                                        }, 3000
                                    );
                                    return;
                                }
                                if (_.values(self.options.nameArray).indexOf(viewLabel) > -1) {
                                    toNewWishlistEl.find('.error-messages').html('<label class="admin__field-error">'
                                        + $t('The wishlist name already exists. ') + '</label>');
                                    clearInterval(window.mpCheckInterval);
                                    window.mpCheckInterval = setInterval(
                                        function () {
                                            toNewWishlistEl.find('.admin__field-error').remove();
                                            clearInterval(window.mpCheckInterval);
                                        }, 3000
                                    );
                                    return;
                                }
                                newCategoryId                         = date.getTime() + '_' + date.getMilliseconds();
                                self.options.nameArray[newCategoryId] = viewLabel;
                                viewHtml                              = self.getCatHtml(newCategoryId, viewLabel);
                                listCat.append(viewHtml);
                            }
                            selectCatModal.data('mageModal').closeModal();
                            $.ajax(
                                {
                                    url: self.options.wishlistUrl,
                                    method: 'POST',
                                    showLoader: type === 'move',
                                    data: {
                                        type: type,
                                        itemId: itemId,
                                        fromCategoryId: fromCategoryId,
                                        fromCategoryName: fromCategoryName,
                                        toCategoryId: toCategoryId,
                                        toCategoryName: toCategoryName,
                                        newCategoryId: newCategoryId,
                                        newCategoryName: viewLabel
                                    },
                                    success: function (res) {
                                        if (type === 'copy') {
                                            return;
                                        }
                                        self.resetProductGrid(res);
                                    },
                                    error: function () {
                                        self.reloadPage();
                                    }
                                }
                            );
                        }
                    );
                },
                editProductObs: function () {
                    var self = this;

                    bodyEl.on('click', 'a.action.edit', function (e) {
                            var url = $(this).attr('href'),
                                categoryId = self.getActiveCatId();

                            e.preventDefault();
                            e.stopPropagation();
                            url += '?categoryId=' + categoryId;
                            window.location.href = url;
                        }
                    );
                },
                shareWishlistObs: function () {
                    bodyEl.on('click', 'button.action.share', function (e) {
                            var options = {
                                'type': 'popup',
                                'responsive': true,
                                'innerScroll': true,
                                'buttons': []
                            };

                            e.preventDefault();
                            e.stopPropagation();
                            modal(options, $('.mp-sharing-wishlist')).openModal();
                        }
                    );
                },
                shareWishlistSubmit: function () {
                    var self = this;

                    bodyEl.on('click', '.mp-sharing-wishlist button.action.submit.primary', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            $.ajax(
                                {
                                    url: self.options.shareWishlistUrl,
                                    method: 'POST',
                                    showLoader: true,
                                    data: 'categoryId=' + self.getActiveCatId() + '&'
                                        + jQuery('.mp-sharing-wishlist :input').serialize(),
                                    success: function (res) {
                                        if (res.errMessage) {
                                            $('.mp-sharing-wishlist .messages').html(
                                                '<div class="message-error error message" data-ui-id="message-error">' +
                                                '   <div>' + res.errMessage + '</div>' +
                                                '</div>'
                                            );
                                        }
                                        if (!res.error) {
                                            $('.mp-sharing-wishlist').data('mageModal').closeModal();
                                        }

                                        if (res.backUrl) {
                                            window.location.href = res.backUrl;
                                        }
                                    },
                                    error: function () {
                                        self.reloadPage();
                                    }
                                }
                            );
                        }
                    );
                },
                updateWishlistObs: function () {
                    var self = this;

                    bodyEl.on('click', 'button.action.update', function (e) {
                            var data = $(this).parents('form').serialize();

                            e.preventDefault();
                            e.stopPropagation();
                            data += '&fromCategoryId=' + self.getActiveCatId();
                            $.ajax(
                                {
                                    url: self.options.updateWishlistUrl,
                                    method: 'POST',
                                    showLoader: true,
                                    data: data,
                                    success: function (res) {
                                        if (res.backUrl) {
                                            window.location.href = res.backUrl;
                                        }
                                        customerData.reload(['customer', 'wishlist'], false);
                                        self.resetProductGrid(res);
                                    },
                                    error: function () {
                                        self.reloadPage();
                                    }
                                }
                            );
                        }
                    );
                },
                addAllToCartObs: function () {
                    var self = this;

                    bodyEl.on('click', '[data-role="all-tocart"]', function (e) {
                            var urlParams = self.options.addAllToCartUrl,
                                separator = urlParams.action.indexOf('?') >= 0 ? '&' : '?';

                            e.preventDefault();
                            e.stopPropagation();
                            $('form#wishlist-view-form.form-wishlist-items').find(
                                '[data-role=qty]').each(function (index, element) {
                                urlParams.action += separator + $(element).prop('name')
                                    + '=' + encodeURIComponent($(element).val());
                                separator = '&';
                            });
                            urlParams.action += separator + 'fromCategoryId=' + $('input[name="categoryId"]').val();
                            dataPost().postData(urlParams);
                        }
                    );
                },
                editCatEnterObs: function () {
                    categoryTableEl.on('keyup', '.action-dropdown-menu-item-edit input', function (e) {
                        if (e.keyCode !== 13) {
                            return;
                        }
                        $(this).siblings('.action-submit').trigger('click');
                    });
                }
            }
        );
        return $.mageplaza.wishlistCategory;
    }
);
