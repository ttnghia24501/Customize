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
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'mage/translate',
    "Magento_Sales/order/create/form",
    'mpIonRangeSlider'
], function ($, ko, _, Component, $t) {
    'use strict';

    var self;

    return Component.extend({
        defaults: {
            template: 'Mageplaza_RewardPoints/spending-points'
        },
        rules: ko.observableArray(),
        selectedRule: ko.observable(),
        balance: ko.observable(),
        pointSpent: ko.observable(),
        canVisibleSpendPoints: ko.observable(true),
        isDisplaySlider: ko.observable(true),
        useMaxPoint: ko.observable(true),
        slider: null,
        oldValue: null,
        isChangeRule: false,
        balanceFormatted: function () {
            var label        = $t('You have %s'),
                rewardConfig = self.rewardSpendingConfig,
                pattern, point, points;

            pattern = rewardConfig.pattern || {single: "{point} point", plural: "{point} points"};
            point   = rewardConfig.balance || 0;
            points  = pattern.single.replace('{point}', point);

            // eslint-disable-next-line radix
            if (parseInt(point) > 1) {
                points = pattern.plural.replace('{point}', point);
            }

            $('.points').html(label.replace('%s', '<strong>' + points + '</strong>'));
        },

        /**
         * Initialize
         */
        initialize: function () {
            this._super();
            self = this;
            this.initData(self.rewardSpendingConfig.spending);
            this.canSpendPoints = ko.computed(function () {
                return !_.isEmpty(self.rewardSpendingConfig.spending) && this.rules().length && this.selectedRule();
            }, this);

            if (!this.canSpendPoints()) {
                $('#order-mp_reward_spend_points').hide();
            } else {
                $('#order-mp_reward_spend_points').show();
            }

            if (this.selectedRule() && !this.selectedRule().isDisplaySlider) {
                $('.reward-spending-slider').hide();
            }

            window.order.applyCoupon = function (code) {
                this.loadArea(['items', 'shipping_method', 'totals', 'billing_method', 'mp_reward_spend_points'], true, {
                    'order[coupon][code]': code,
                    reset_shipping: 0
                });
                this.orderItemChanged = false;
                jQuery('html, body').animate({
                    scrollTop: 0
                });
            };

            window.order.productConfigureSubmit = function (listType, area, fieldsPrepare, itemsFilter) {
                var fields = [], url, name;

                area.push('mp_reward_spend_points');
                // prepare loading areas and build url
                area              = this.prepareArea(area);
                this.loadingAreas = area;
                url               = this.loadBaseUrl + 'block/' + area + '?isAjax=true';

                // prepare additional fields
                fieldsPrepare                = this.prepareParams(fieldsPrepare);
                fieldsPrepare.reset_shipping = 1;
                fieldsPrepare.json           = 1;

                // create fields
                for (name in fieldsPrepare){
                    fields.push(new Element('input', {type: 'hidden', name: name, value: fieldsPrepare[name]}));
                }
                productConfigure.addFields(fields);

                // filter items
                if (itemsFilter) {
                    productConfigure.addItemsFilter(listType, itemsFilter);
                }

                // prepare and do submit
                productConfigure.addListType(listType, {urlSubmit: url});
                productConfigure.setOnLoadIFrameCallback(listType, function (response) {
                    this.loadAreaResponseHandler(response);
                }.bind(this));
                productConfigure.submit(listType);
                // clean
                this.productConfigureAddFields = {};
            };
        },

        /**
         * @param value
         */
        initData: function (value) {
            var slidePoint;

            this.rules(value.rules);
            this.initSelectedRule(value.ruleApplied);
            slidePoint = value.pointSpent;
            if (this.selectedRule() &&
                Number(slidePoint) > Number(this.selectedRule().max) &&
                this.selectedRule().isDisplaySlider
            ) {
                slidePoint = this.selectedRule().max;
            }

            self.pointSpent(slidePoint);

            if (this.selectedRule() &&
                this.selectedRule().isDisplaySlider
            ) {
                this.initSlider();
                this.isDisplaySlider(this.selectedRule().isDisplaySlider);
            }

            $('input.mp-spent').val(slidePoint);
            self.checkMaxPointByAction();
            this.balanceFormatted();
            if (value.rules.length === 1) {
                $('#reward-spending-rules').hide();
                $('span.select-rule').text(this.selectedRule().label);
            }

            if (value.rules.length > 1) {
                $.each(value.rules, function (i, rule) {
                    var label    = rule.label,
                        selected = '';

                    if (!label) {
                        label = ' ';
                    }

                    if (self.selectedRule() && self.selectedRule().id === rule.id) {
                        selected = 'selected';
                    }

                    $('#reward-spending-rules').append('<option value="' + rule.id + '" ' + selected + '>'
                        + label + '</option>');
                });
            }

            if (value.rules.length) {
                $('input.mp-spent').change(function (e) {
                    self.changePointSpent(this, e);
                });

                $('#reward-use-max-point').change(function (e) {
                    self.changeMaxPoint(this, e);
                });

                $('#reward-spending-rules').change(function (e) {
                    self.changeRule(this, e);
                });
            }
        },

        changePointSpent: function (obj, event) {
            var rule,
                value,
                newValue;

            if (event && event.originalEvent) {
                rule = this.selectedRule();

                // eslint-disable-next-line radix
                if (obj.value && parseInt(obj.value)) {
                    // eslint-disable-next-line radix
                    value = parseInt(obj.value);

                    if (value < rule.min) {
                        newValue = rule.min;
                    } else if (value > rule.max) {
                        newValue = rule.max;
                    } else {
                        newValue = value;
                    }
                } else {
                    newValue = rule.min;
                }
                $('input.mp-spent').val(newValue);

                if (newValue !== this.slider.old_from) {
                    this.updateValueOnSlider(newValue);
                }
            }
        },

        changeMaxPoint: function (obj, event) {
            var newValue;

            self.initSelectedRule($('#reward-spending-rules').val());

            if (event && event.originalEvent) {
                newValue = $(obj).is(':checked') ? self.selectedRule().max : self.selectedRule().min;
                this.updateValueOnSlider(newValue);
                $('input.mp-spent').val(newValue);
            }
        },

        updateValueOnSlider: function (newValue) {
            self.pointSpent(newValue);
            this.slider.update({from: newValue});
        },

        /**
         * @param obj
         * @param event
         */
        changeRule: function (obj, event) {
            var rule;

            this.initSelectedRule(obj.value);

            if (event && event.originalEvent) {
                rule = this.selectedRule();
                if (!rule) {
                    return;
                }

                self.initSlider();

                if (this.slider) {
                    self.pointSpent(rule.min);
                    $('.reward-spending-slider').hide();
                    if (rule.isDisplaySlider) {
                        $('.reward-spending-slider').show();
                    }
                    this.isDisplaySlider(rule.isDisplaySlider);
                    this.slider.update({
                        min: rule.min,
                        max: rule.max,
                        from: 0
                    });
                    this.isChangeRule = true;
                }
            }
        },

        /**
         * @param ruleId
         * @return {exports}
         */
        initSelectedRule: function (ruleId) {
            var selectedRule;

            if (this.rules() && this.rules().length) {
                if (ruleId) {
                    $.each(this.rules(), function (index, rule) {
                        if (rule.id === ruleId) {
                            selectedRule = rule;
                            return false;
                        }
                    });
                }
                if (!selectedRule) {
                    selectedRule = this.rules()[0];
                }

                if (!_.isEqual(selectedRule, this.selectedRule())) {
                    this.selectedRule(selectedRule);
                }
            } else {
                this.selectedRule(null);
            }

            return this;
        },

        /**
         * Init spend slider
         */
        initSlider: function () {
            var range                = $(".reward-range-slider"),
                rangeFinishFirstTime = true,
                isUseMaxPointByDefault,
                isUseMaxPointByAction;

            range.ionRangeSlider({
                type: "single",
                min: 0,
                max: 0,
                from: 0,
                step: 0,
                onChange: function (data) {
                    self.pointSpent(data.from);
                    $('input.mp-spent').val(data.from);
                },
                onFinish: function () {
                    if (rangeFinishFirstTime) {
                        return;
                    }
                    self.checkMaxPointByAction();
                    self.sendUpdateSpentPoints();
                },
                onUpdate: function () {
                    if (rangeFinishFirstTime) {
                        return;
                    }
                    self.checkMaxPointByAction();
                    self.sendUpdateSpentPoints();

                }
            });
            this.slider = range.data("ionRangeSlider");
            self.updateSlider();
            isUseMaxPointByDefault = self.pointSpent() === null && self.rewardSpendingConfig.spending.useMaxPoints;
            isUseMaxPointByAction  = Number(self.pointSpent()) === this.selectedRule().max;
            if (isUseMaxPointByDefault) {
                self.pointSpent(this.selectedRule().max);
                this.updateSlider();
            }
            this.useMaxPoint(isUseMaxPointByDefault || isUseMaxPointByAction);
            rangeFinishFirstTime = false;

            this.sendUpdateSpentPoints();
        },

        updateSlider: function () {
            var rule = this.selectedRule();

            if (rule) {
                this.slider.update({
                    min: rule.min,
                    max: rule.max,
                    step: rule.step,
                    from: self.pointSpent()
                });
            }
            this.sendUpdateSpentPoints();
        },

        sendUpdateSpentPoints: function () {
            if (self.pointSpent() !== this.oldValue || this.isChangeRule ||  !$('input.mp-spent').val()) {
                this.oldValue = self.pointSpent();
                $('input.mp-spent').val(self.pointSpent());
                window.order.loadArea(['totals'], true, {
                    'mp_reward_spend_points': self.pointSpent(),
                    'mp_reward_spend_rateId': this.selectedRule().id
                });
            }
        },

        checkMaxPointByAction: function () {
            if (this.selectedRule()) {
                this.useMaxPoint(self.pointSpent() === this.selectedRule().max);
                if (parseInt(self.pointSpent()) === parseInt(this.selectedRule().max)) {
                    $('#reward-use-max-point').prop('checked', true);
                } else {
                    $('#reward-use-max-point').prop('checked', false);
                }
            }
        }
    });
});
