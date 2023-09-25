define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/model/customer',
    'Growcode_CreateAccountAfterPlaceOrder/js/model/checkout/email'
], function ($, Component, customer, email) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Growcode_CreateAccountAfterPlaceOrder/account-registration-form',
            isCustomerLoggedIn: customer.isLoggedIn,
            isPasswordVisible: false,
            isFormVisible: false
        },
        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super().observe(['isFormVisible', 'isPasswordVisible']);

            email.isExist.subscribe(function(value) {
                this.isPasswordVisible(value);
            }.bind(this));

            return this;
        }
    });
});
