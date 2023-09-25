var config = {
    map: {
        "*": {
            'Magento_Checkout/template/shipping.html': 'Growcode_CreateAccountAfterPlaceOrder/template/shipping-override.html'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Growcode_CreateAccountAfterPlaceOrder/js/model/shipping-save-processor/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Growcode_CreateAccountAfterPlaceOrder/js/view/validate-account-registration-mixin': true
            },
            'Magento_Checkout/js/view/form/element/email': {
                'Growcode_CreateAccountAfterPlaceOrder/js/view/form/element/email-mixin': true
            }
        }
    }
};
