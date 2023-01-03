define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    let config = window.checkoutConfig.payment;

    if (config['merchant_warrior_payframe'] && config['merchant_warrior_payframe'].active) {
        if (config['merchant_warrior_payframe'].isSandBoxEnabled) {
            rendererList.push(
                {
                    type: 'merchant_warrior_payframe',
                    component: 'MerchantWarrior_Payment/js/view/payment/method-renderer/payframeSandbox'
                }
            );
        } else {
            rendererList.push(
                {
                    type: 'merchant_warrior_payframe',
                    component: 'MerchantWarrior_Payment/js/view/payment/method-renderer/payframe'
                }
            );
        }
    }

    /** Add view logic here if needed */
    return Component.extend({});
});
