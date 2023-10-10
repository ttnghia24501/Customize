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
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/totals',
        'Mageplaza_RewardPoints/js/model/points'
    ], function (Component, totals, points) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Mageplaza_RewardPoints/totals/label'
            },

            /**
             * Get earning segment
             * @returns {*}
             */
            getEarning: function () {
                return totals.getSegment('mp_reward_earn');
            },

            /**
             * Get earning point formatted
             * @returns {*}
             */
            getEarningValue: function () {
                var point = this.getEarning().value;

                return points.format(point);
            },

            /**
             * Get earning point formatted
             * @returns {*}
             */
            checkIsLogin: function () {

                return totals.totals()['extension_attributes']['have_highlight'];
            },

            /**
             * Get spending segment
             * @returns {*}
             */
            getSpending: function () {
                return totals.getSegment('mp_reward_spent');
            },

            /**
             * Get spending point formatted
             * @returns {*}
             */
            getSpendingValue: function () {
                var point = this.getSpending().value;

                return points.format(point);
            },

            /**
             * Get spending segment
             * @returns {*}
             */
            isEarnWithSpent: function () {
                return points.isEarnWithSpent;
            }
        });
    }
);
