define([
    'Magento_Checkout/js/view/payment/default'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MerchantWarrior_Payment/payment/mw_payframe'
        },

        /**
         * Returns payment method instructions.
         *
         * @return {*}
         */
        isActive: function () {
            return window.checkoutConfig.payment.merchant_warrior_payframe.enabled;
        }
    });
});
