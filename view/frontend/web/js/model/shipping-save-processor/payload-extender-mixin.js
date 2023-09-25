define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';
    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (originalProceedToCheckoutFunction, config, element) {
            let payload = originalProceedToCheckoutFunction(config, element);

            payload.addressInformation['extension_attributes']['account_registration_password']
                = $('#new_account_password').val();

            return payload;
        });
    };
});
