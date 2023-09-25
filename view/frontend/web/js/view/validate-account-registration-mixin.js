define([
    'jquery'
], function ($) {
    'use strict';
    const mixin = {
        validateShippingInformation: function () {
            const $accountRegistrationForm = $('#co-account-registration-form');
            if ($accountRegistrationForm.length) {
                return this._super() && $accountRegistrationForm.valid();
            }
            return this._super();
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
