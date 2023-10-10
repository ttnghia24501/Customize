/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/totals'
    ], function (Component, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Mageplaza_RewardPoints/totals/discount'
            },

            /**
             * Get reward discount total
             * @returns {*}
             */
            getTotal: function () {
                return totals.getSegment('mp_reward_discount');
            },

            /**
             * Get reward discount formatted
             * @returns {*|String}
             */
            getValue: function () {
                return this.getFormattedPrice(this.getTotal().value);
            }
        });
    }
);
