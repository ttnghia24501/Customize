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
 * @package     Mageplaza_RecentSalesNotification
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'mage/template',
        'mage/storage',
        'moment'
    ], function ($, mageTemplate, storage, moment) {
        'use strict';
        $.widget(
            'mageplaza.RecentSalesNotification', {
                options: {
                    isCloseBtnClicked: false,
                    count: [],
                    total: 0,
                    inter: 0,
                    timeout: 0,
                    random: 0,
                    recentsalesnotificationData: []
                },
                _create: function () {
                    this._loadAjax();
                },

                _loadAjax: function () {
                    var self = this;

                    // compatible with quick view module
                    if (this.element.closest('#mpquickview-popup #maincontent').length) {
                        return;
                    }

                    $.ajax(
                        {
                            url: this.options.url,
                            type: 'POST',
                            data: {
                                popups: this.options.popups,
                                productId: this.options.currentProductId,
                                device: this.checkDevice() ? 1 : 0
                            },
                            dataType: 'json',
                            cache: false,
                            success: function (response) {
                                if (!response || response.length === 0) {
                                    return;
                                }

                                self.options.recentsalesnotificationData = response;
                                self.processPopup();
                            },
                            error: function (response) {
                                console.error(response);
                            }
                        }
                    );
                },

                processPopup: function () {
                    var self      = this,
                        length    = self.options.recentsalesnotificationData.length,
                        rand      = length === 1 ? 0 : Math.floor(Math.random() * length),
                        popupList = self.options.recentsalesnotificationData[rand].popupList,
                        popupData = self.options.recentsalesnotificationData[rand].popupData;

                    if (this.options.isCloseBtnClicked) {
                        return;
                    }
                    if (!self.options.count[rand]) {
                        self.options.count[rand] = 0;
                    }
                    if (self.options.total < self.options.recentsalesnotificationData.popupTotal
                        && self.options.count[rand] >= popupList.length
                    ) {
                        return this.processPopup();
                    } else if (self.options.total >= self.options.recentsalesnotificationData.popupTotal) {
                        self.options.count                       = [];
                        self.options.total                       = 0;
                        self.options.inter                       = 0;
                        self.options.timeout                     = 0;
                        self.options.random                      = 0;
                        self.options.recentsalesnotificationData = [];
                        return setTimeout(function () {
                            self._loadAjax();
                        }, 60000);
                    }

                    if (!self.element.hasClass('mprecentsalesnotification-popup-' + popupData.position)) {
                        self.element.addClass('mprecentsalesnotification-popup-' + popupData.position);
                    }
                    self.options.random = rand;

                    var randomPopupIndex = Math.floor(Math.random() * popupList.length);
                    self.getTemplate(popupList[randomPopupIndex], popupData);

                    self.showPopup();
                    if (popupData.showCloseBtn === '1') {
                        self.eventClosePopup();
                    }
                    // play popup sound
                    if (popupData.enableSound === '1') {
                        self.playSound();
                    }
                },

                /**
                 * get popup template html
                 * @param data
                 * @param popupData
                 * @returns {*}
                 */
                getTemplate: function (data, popupData) {
                    var template, elementId,
                        childClass = popupData.isChild ? 'mprecentsalesnotification-popup-child' : '';

                    if (data && data.time) {
                        data.time    = moment.utc(data.time).local().format('YYYY-MM-DD HH:mm:ss');
                        data.content = data.content.replace("{{time}}", moment(data.time).fromNow());
                    }

                    elementId = new Date().valueOf();
                    template  = mageTemplate(
                        '#mprecentsalesnotification-popup-template',
                        {
                            data: {
                                id: elementId,
                                popupId: popupData.popupId,
                                position: popupData.position,
                                showCloseBtn: popupData.showCloseBtn === '1',
                                childClass: childClass,
                                product_id: data.product_id,
                                product_image: data.product_image,
                                checkout_image: data.checkout_image,
                                product_name: data.product_name,
                                product_url: data.product_url,
                                content: data.content
                            }
                        }
                    );

                    this.element.html(template);
                    // update report with click event
                    this.initObserver();
                    this.element.trigger('mprecentsalesnotificationUpdated');
                    this.setPopupCss(elementId, popupData);
                },

                setPopupCss: function (elementId, popupData) {
                    var popupElement = '#mprecentsalesnotification-popup-' + elementId;

                    if (popupData.backgroundImg) {
                        $(popupElement).css('background-image', 'url(' + popupData.backgroundImg + ')');
                    }
                    if (popupData.borderColor) {
                        $(popupElement).css('border-color', popupData.borderColor);
                    }
                    if (popupData.textColor) {
                        $(popupElement).find('.mprecentsalesnotification-popup-content').css('color', popupData.textColor);
                    }
                    if (popupData.hoverColor) {
                        $(popupElement).find('.mprecentsalesnotification-popup-content a').hover(function () {
                            $(this).css("color", popupData.hoverColor);
                        }, function () {
                            $(this).css("color", popupData.textColor);
                        });
                    }
                },

                showPopup: function () {
                    var self        = this,
                        popupData   = self.options.recentsalesnotificationData[self.options.random].popupData,
                        displayTime = parseInt(popupData.displayTime, 10) * 1000;

                    if (!self.options.isCloseBtnClicked) {
                        clearTimeout(self.options.inter);
                        self.addAnimation(popupData, 'show');
                        self.options.timeout = setTimeout(function () {
                            self.hidePopup();
                        }, displayTime);
                    }
                },

                hidePopup: function () {
                    var self       = this,
                        popupData  = self.options.recentsalesnotificationData[self.options.random].popupData,
                        switchTime = parseInt(popupData.switchTime, 10) * 1000;

                    clearTimeout(self.options.timeout);
                    self.addAnimation(popupData, 'hide');
                    self.options.inter = setTimeout(function () {
                        self.options.count[self.options.random]++;
                        self.options.total++;
                        self.processPopup();
                    }, switchTime);
                },

                /**
                 * Check client device
                 * @returns {boolean}
                 */
                checkDevice: function () {
                    var check = false;

                    (function (a) {
                        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true;
                    })(navigator.userAgent || navigator.vendor || window.opera);
                    return check;
                },

                /**
                 * Event customer close popup
                 */
                eventClosePopup: function () {
                    var self      = this,
                        popupData = self.options.recentsalesnotificationData[self.options.random].popupData;

                    self.element.find('.mprecentsalesnotification-close').on('click', function () {
                        clearTimeout(self.options.timeout);
                        self.addAnimation(popupData, 'hide');
                        self.options.isCloseBtnClicked = true;
                        self.updateReport(
                            'close',
                            $(this).parents('.mprecentsalesnotification-popup-floating').attr('data-popup-id')
                        );
                    });
                },

                /**
                 * init click observer
                 */
                initObserver: function () {
                    var self = this;

                    self.element.on('mprecentsalesnotificationUpdated', function () {
                        var clickEl = self.element.find('a, button');

                        clickEl.click(function () {
                            self.updateReport(
                                'click',
                                $(this).parents('.mprecentsalesnotification-popup-floating').attr('data-popup-id')
                            );
                        });
                    });

                    self.element.find('.mprecentsalesnotification-popup-floating').on("mouseenter", function () {
                        window.clearTimeout(self.options.timeout);
                    }).on("mouseleave", function () {
                        self.showPopup();
                    });
                },

                /**
                 * put request to update report
                 * @param event
                 * @param popupId
                 */
                updateReport: function (event, popupId) {
                    var url = 'mprecentsalesnotification/ajax/' + event;

                    storage.put(url, JSON.stringify({popup_id: popupId}), false);
                },

                /**
                 * Process Popup animation
                 * @param popupData
                 * @param status
                 */
                addAnimation: function (popupData, status) {
                    var animation = popupData.animation;

                    switch (animation){
                        case 'slide':
                            status === 'show' ? this.element.slideDown() : this.element.slideUp();
                            break;
                        case 'fade':
                            status === 'show' ? this.element.fadeIn() : this.element.fadeOut();
                            break;
                        case 'zoom':
                            if (status === 'show') {
                                this.element.css({display: 'block', opacity: '0', zoom: '30%'});
                                this.element.animate({zoom: '100%', opacity: '1'});
                            } else {
                                this.element.animate({zoom: '30%', opacity: "0"});
                            }
                            break;
                    }
                },

                /**
                 * play popup sound
                 */
                playSound: function () {
                    var audio       = $('#mprecentsalesnotification-sound')[0],
                        playPromise = audio.play();

                    if (playPromise !== undefined) {
                        playPromise.then(function () {
                            audio.muted = false;
                            audio.play();
                        })
                        .catch(function () {
                            console.warn('The play method is not allowed by the user agent or the platform in the current context, possibly because the user denied permission.');
                        });
                    }
                }
            }
        );

        return $.mageplaza.RecentSalesNotification;
    }
);
