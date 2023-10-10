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
        'Magento_Ui/js/grid/provider'
    ], function ($, Element) {
        'use strict';

        return Element.extend(
            {
                reload: function (option) {
                    this.setMpFilterParams();
                    this._super(option);
                },
                setMpFilterParams: function () {
                    var dateRangEl = $('#daterange');

                    $('.chart-container-all').hide();
                    if (typeof this.params.mpFilter === "undefined") {
                        this.params.mpFilter = {};
                    }
                    if (dateRangEl.length) {
                        if (typeof this.params.mpFilter.startDate === "undefined") {
                            this.params.mpFilter.startDate = dateRangEl.data().startDate.format('Y-MM-DD');
                        }
                        if (typeof this.params.mpFilter.endDate === "undefined") {
                            this.params.mpFilter.endDate = dateRangEl.data().endDate.format('Y-MM-DD');
                        }
                        if (typeof this.params.mpFilter.store === "undefined") {
                            this.params.mpFilter.store = $('#store_switcher').val();
                        }
                        if (typeof this.params.mpFilter.customer_group_id === "undefined") {
                            this.params.mpFilter.customer_group_id = $('.customer-group select').val();
                        }
                    }
                }
            }
        );
    }
);
