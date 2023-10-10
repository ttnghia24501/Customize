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
 * @package     Mageplaza_BetterWishlist
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
], function ($) {
    'use strict';
    return function (RedirectUrl) {
        $.widget('mage.redirectUrl', RedirectUrl, {
            _onEvent: function () {
                if((this.element.hasClass('limiter-options')
                    && this.element.parents('.wishlist-toolbar').length
                    && $('.mp-wishlist-category').length)
                    || (this.element.hasClass('mp-wishlist-form') || this.element.parents('.mp-wishlist-form').length)
                ){
                    return;
                }
                return this._super();
            }
        });

        return $.mage.redirectUrl;
    };
});
