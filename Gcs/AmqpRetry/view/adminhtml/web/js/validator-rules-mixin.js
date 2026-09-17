define([
    'jquery'
], function ($) {
    'use strict';
    return function (target) {
        $.validator.addMethod(
            'validate-numbers-and-commas',
            function (value) {
                return /^[0-9,]+$/.test(value);
            },
            $.mage.__('Please enter only numbers and commas.')
        );
        return target;
    };
});
