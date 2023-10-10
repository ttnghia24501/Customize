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
    'mage/translate'
], function ($) {
    "use strict";

    $.widget('mageplaza.initCategoryMap', {

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.init();
            this.collapse();
        },
        init: function () {
            var self = this;

            $('#category_map .category-tree input').each(function () {
                if (self.options.categoryMap[$(this).attr('id')] !== undefined) {
                    $(this).val(self.options.categoryMap[$(this).attr('id')]);
                }
            });
        },
        collapse: function () {
            $('#category_map .collapse').click(function () {
                if ($(this).hasClass('fa-minus')) {
                    $(this).removeClass('fa-minus');
                    $(this).addClass('fa-plus');
                } else {
                    $(this).removeClass('fa-plus');
                    $(this).addClass('fa-minus');
                }
                $(this).parent().siblings('.category-tree-container').toggle();
            });
        },
    });

    return $.mageplaza.initCategoryMap;
});
