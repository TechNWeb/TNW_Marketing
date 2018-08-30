/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
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
        },

        mouseOver: function(data, event) {
            var onStar = parseInt(data.value, 10);

            // Now highlight all the stars that's not after the current hovered star
            $(event.currentTarget).parent().children('li.star').each(function(e){
                if (e < onStar) {
                    $(this).addClass('hover');
                }
                else {
                    $(this).removeClass('hover');
                }
            });
        },

        mouseOut: function(data, event) {
            $(event.currentTarget).parent().children('li.star').each(function(e){
                $(this).removeClass('hover');
            });
        },

        mouseClick: function(data, event) {
            var onStar = parseInt(data.value, 10); // The star currently selected
            var stars = $(event.currentTarget).parent().children('li.star');

            for (var i = 0; i < stars.length; i++) {
                $(stars[i]).removeClass('selected');
            }

            for (i = 0; i < onStar; i++) {
                $(stars[i]).addClass('selected');
            }

            this.value(onStar);
        }
    });
});
