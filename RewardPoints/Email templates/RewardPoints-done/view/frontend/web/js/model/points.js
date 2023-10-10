/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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
define(['jquery', 'ko', 'Magento_Checkout/js/model/quote'], function ($, ko, quote) {
    "use strict";

    var pattern, isEarnWithSpent,
        balance            = ko.observable(),
        rewardPointsConfig = ko.computed(function () {
            var rewardConfig = {},
                extensionAttributes = quote.getTotals()().extension_attributes;
            
            if (extensionAttributes && extensionAttributes.reward_points) {
                rewardConfig = JSON.parse(extensionAttributes.reward_points);
            }

            pattern = rewardConfig.pattern || {single: "{point} point", plural: "{point} points"};
            balance(rewardConfig.balance || 0);
            isEarnWithSpent= rewardConfig.isEarnWithSpent;
            return rewardConfig.spending || {};
        });

    return {
        spendingConfig: rewardPointsConfig,
        balance: balance,
        isEarnWithSpent : isEarnWithSpent,
        isCheckoutCart: $('body').hasClass('checkout-cart-index'),

        /**
         * Format point label
         * @param point
         * @return {string}
         */
        format: function (point) {
            if (parseInt(point) > 1) {
                return pattern.plural.replace('{point}', point);
            }

            return pattern.single.replace('{point}', point);
        }
    };
});
