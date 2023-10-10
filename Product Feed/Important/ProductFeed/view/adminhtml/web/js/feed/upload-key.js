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
    'jquery'
], function ($) {
    "use strict";

    $.widget('mageplaza.uploadKey', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            var self = this;

            $('#add-private-key').on('click', function () {
                $('#feed_private_key_path').click();
            });

            $('#feed_private_key_path').change(function () {
                $('#feed_path_key').val(self.options.path + $(this).val().split('\\').pop());
            });
        },
    });

    return $.mageplaza.uploadKey;
});
