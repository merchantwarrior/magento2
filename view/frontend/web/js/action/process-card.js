define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/customer-data'
    ],
    function (quote, urlBuilder, storage, url, errorProcessor, customerData) {
        'use strict';

        return function (paymentData) {

            let serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});

            return storage.post(
                serviceUrl,
                JSON.stringify(paymentData)
            ).done(
                (response) => {
                    return response;
                }
            ).fail(
                (response) => {
                    errorProcessor.process(response);
                }
            );
        };
    }
);
