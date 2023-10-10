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
 * @package     Mageplaza_CustomerAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'uiRegistry',
    'rjsResolver'
], function($, registry, resolver) {
    'use strict';

    return {
        attributes: window.checkoutConfig ? window.checkoutConfig.mpCaConfig.customerAttributes.dependency : [],

        initialize: function() {
            this._super();

            if (this.attributes.length){
                this.checkDependency();
            }

            resolver(this.afterResolveDocument.bind(this));

            return this;
        },

        afterResolveDocument: function () {
            if (this.attributes.length) {
                this.checkDependency();
            }
        },

        onUpdate: function() {
            this._super();

            if (this.attributes.length){
                this.checkDependency();
            }
        },

        checkDependency: function() {
            var self = this,
                attrId;

            $.each(this.attributes, function(index, attribute) {
                if (attribute.attribute_code === self.index){
                    attrId = attribute.attribute_id;
                }
            });

            $.each(this.attributes, function(index, attribute) {
                if (attribute.field_depend === attrId && attribute.value_depend){
                    var dependElem = registry.get(self.parentName + '.' + attribute.attribute_code);

                    if (dependElem){
                        var valueDepend = attribute.value_depend.split(',');

                        if ($.inArray(attrId + '_' + self.value(), valueDepend) !== -1){
                            dependElem.show();
                        } else {
                            dependElem.value(dependElem.options ? [] : null);
                            dependElem.hide();
                        }
                    }
                }
            });
        }
    };
});
