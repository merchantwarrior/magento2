define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'merchant_warrior_payframe',
            component: 'MerchantWarrior_Payment/js/view/payment/method-renderer/payframe'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
