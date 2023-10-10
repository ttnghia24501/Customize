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
    'moment',
    'uiRegistry',
    'daterangepicker',
    'chartBundle'
], function ($, moment) {
    'use strict';
    var dateRangeEl = $('#daterange');

    $.widget('mageplaza.productfeedmenu', {
        _create: function () {

            this.initNowDateRange(moment(this.options.date[0]), moment(this.options.date[1]));
            this.initDateRangeApply();

            $('body').on('click', function (e) {
                var dateRangElement = $('#daterange'),
                    params = {};

                if ($('.daterangepicker').is(':visible')) {
                    $('.drp-calendar.left').show();
                    $('.drp-calendar.right').show();
                }

                if ($(e.target).parents().hasClass('daterangepicker')) {
                    if (!$('.daterangepicker').is(':visible')) {

                        if (typeof params.mpFilter === 'undefined') {
                            params.mpFilter = {};
                        }

                        params.mpFilter.startDate = dateRangElement.data().startDate.format('');
                        params.mpFilter.endDate = dateRangElement.data().endDate.format('');
                        params.dateRange = [params.mpFilter.startDate, params.mpFilter.endDate, null, null];
                        $('.drp-calendar.eft').show();
                        $('.drp-calendar.right').show();
                    }
                }
            });
        },
        initDateRange: function (el, start, end, data) {
            var self = this,
                dateRangElement = $('#daterange'),
                dateRange;

            function cb(cbStart, cbEnd) {
                el.find('span').html(cbStart.format('MMM DD, YYYY') + ' - ' + cbEnd.format('MMM DD, YYYY'));
            }

            el.daterangepicker(data, cb);
            cb(start, end);
            // draw chart
            dateRange = [dateRangElement.data().startDate.format(''), dateRangElement.data().endDate.format('')];
            self.getReportData(dateRange, self.options.ajaxUrl);
        },
        initNowDateRange: function (start, end) {
            var dateRangeData = {
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ],
                    'YTD': [moment().subtract(1, 'year'), moment()],
                    '2YTD': [moment().subtract(2, 'year'), moment()]
                }
            };

            this.initDateRange(dateRangeEl, start, end, dateRangeData);
        },
        initDateRangeApply: function () {
            var self = this;

            dateRangeEl.on('apply.daterangepicker', function (ev, picker) {
                var params = {};

                self.initNowDateRange(picker.startDate, picker.endDate);
                self.initDateRangeApply();

                if (typeof params.mpFilter === 'undefined') {
                    params.mpFilter = {};
                }

                params.mpFilter.startDate = picker.startDate.format('Y-MM-DD');
                params.mpFilter.endDate = picker.endDate.format('Y-MM-DD');
                params.dateRange = [params.mpFilter.startDate, params.mpFilter.endDate, null, null];
            });
        },
        getReportData: function (date, ajaxUrl) {
            var self = this;

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {date: date}
            }).done(function (data) {
                // Create table
                $('#table-reports table').remove();
                $('#table-reports').html(data.table);
                // Draw chart
                self.drawChart(data.chart);
            });
        },
        drawChart: function (data) {
            var reportChart = $("#reportChart"),
                backgroundColors = [
                    '#4285f4', '#ea4335', '#fbbc04', '#34a853', '#ff6d01',
                    '#46bdc6', '#7baaf7', '#f07b72', '#fcd04f', '#71c287',
                    '#ff994d', '#7ed1d7', '#b3cefb', '#f7b4ae', '#fde49b',
                    '#aedcba', '#ffc599', '#b5e5e8', '#ecf3fe', '#fdeceb'
                ];

            if (typeof window.reportChart !== 'undefined' && typeof window.reportChart.destroy === 'function') {
                window.reportChart.destroy();
            }

            if (data.chartLabel && data.revenue) {
                window.reportChart = new window.Chart(reportChart, {
                    type: 'pie',
                    data: {
                        labels: data.chartLabel,
                        datasets: [
                            {
                                data: data.revenue,
                                fill: true,
                                backgroundColor: backgroundColors,
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        legend: {
                            display: true,
                            position: 'right'
                        }
                    }
                });
                reportChart.show(100);
            } else {
                reportChart.hide(100);
            }
        }
    });

    return $.mageplaza.productfeedmenu;
});
