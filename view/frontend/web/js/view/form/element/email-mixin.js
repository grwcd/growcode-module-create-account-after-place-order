define([
    'Growcode_CreateAccountAfterPlaceOrder/js/model/checkout/email'
], function (email) {
    'use strict';

    var mixin = {
        initObservable: function () {
            this._super();

            email.isExist(this.isPasswordVisible());

            this.isPasswordVisible.subscribe(function (value) {
                email.isExist(value)
            })

            return this;
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
