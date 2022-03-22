define(
    [
        'mage/storage',
        'Magento_Checkout/js/model/url-builder',
        'mage/url',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (storage, urlBuilder, url, errorProcessor) {
        'use strict';

        return function (data) {
            let serviceUrl = urlBuilder.createUrl('/merchant-warrior/payment/process/card', {});

            return storage.post(
                serviceUrl, JSON.stringify(data), true, 'application/json'
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
