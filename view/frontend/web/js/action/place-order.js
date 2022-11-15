define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/customer-data',
    ],
    function (quote, urlBuilder, storage, url, errorProcessor, customer, fullScreenLoader, customerData) {
        'use strict';

        return function (paymentData) {
            let serviceUrl, payload;

            payload = {
                cartId: quote.getQuoteId(),
                billingAddress: quote.billingAddress(),
                paymentMethod: paymentData
            };

            /** Checkout for guest and registered customer. */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                    quoteId: quote.getQuoteId()
                });
                payload.email = quote.guestEmail;
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
            }

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                (response) => {
                    let clearData = {
                        'selectedShippingAddress': null,
                        'shippingAddressFromData': null,
                        'newCustomerShippingAddress': null,
                        'selectedShippingRate': null,
                        'selectedPaymentMethod': null,
                        'selectedBillingAddress': null,
                        'billingAddressFromData': null,
                        'newCustomerBillingAddress': null
                    };

                    if (response.responseType !== 'error') {
                        customerData.set('checkout-data', clearData);

                        window.location.replace(url.build(
                            window.checkoutConfig.payment.merchant_warrior_payframe.successPage
                        ));
                    }
                }
            ).fail(
                (response) => {
                    errorProcessor.process(response);
                }
            ).always(
                () => {
                    fullScreenLoader.stopLoader(true);
                }
            );
        };
    }
);
