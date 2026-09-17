require(
    [
        'Magento_Ui/js/lib/validation/validator',
        'jquery',
        'mage/translate'
    ], function (validator, $) {
        validator.addRule(
            'validate-numbers-and-commas',
            function (value) {
                return value === '' || (Array.isArray(value) && value.length === 1 && value[0] === '')
                    || /^[0-9,]+$/.test(value);
            },
            $.mage.__('Please enter only numbers and commas.')
        );
    });
