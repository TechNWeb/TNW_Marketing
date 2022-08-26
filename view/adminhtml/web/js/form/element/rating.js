/*
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/select'
], function ($, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            elementTmpl: 'TNW_Marketing/form/element/rating',
            hovered: 0
        },
        mouseOver: function(data, event) {
             this.hovered = parseInt(data.value, 10);
             this.value.valueHasMutated();
        },
        mouseOut: function(data, event) {
            this.hovered = 0;
            this.value.valueHasMutated();
        },

        checkIsSelected: function(value) {

            value = parseInt(value, 10);
            var definedValue = parseInt(this.value(), 0);

            return (value <= definedValue);
        },

        checkIsHovered: function(value) {

            value = parseInt(value, 10);
            var hoveredValue = parseInt(this.hovered, 0);

            return (value <= hoveredValue);
        },

        mouseClick: function(data, event) {
            var onStar = parseInt(data.value, 10); // The star currently selected

             this.value(onStar);
        }
    });
});
