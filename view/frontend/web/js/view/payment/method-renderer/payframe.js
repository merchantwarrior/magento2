define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'MerchantWarrior_Payment/js/action/place-order',
    'MerchantWarrior_Payment/js/action/process-card',
    'payframeLib'
], function ($, Component, customerData, quote, placeOrderAction, processCardAction) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MerchantWarrior_Payment/payment/mw_payframe',
            mwIframeID: 'mwIframe',
            mwCardDivId: 'payframe-card-data',
            frameStyle: {
                backgroundColor: '#ffbfec',
                textColor: '#f963cd',
                border: '3px solid #f963cd',
                fontFamily: 'Arial',
                errorTextColor: 'red',
                errorBorderColor: 'red',
                fontSize: '18px',
                width: '400px',
                cardTypeDisplay: 'right',
                padding: '5px',
                fieldHeight: '60px'
            }
        },
        mwPayframe: '',
        method: 'getPayframeToken', // change this to getPayframeToken for payment payframe
        threeDS: false, // this will only work with the getPayframeToken method

        /**
         * Init component
         */
        initialize: function () {
            this._super();

            this.isChecked.subscribe(function(methodCode) {
                if (methodCode === 'merchant_warrior_payframe') {
                    this._initMwPayFrame();
                }
            }.bind(this));
        },

        /**
         * After render action
         */
        initForm: function () {
            if (this.isChecked() === 'merchant_warrior_payframe') {
                this._initMwPayFrame();
            }
        },

        /**
         * Initialize Pay Frame object
         *
         * @private
         */
        _initMwPayFrame: function () {
            this.mwPayframe = this._initPayFrame(
                this._getPaymentConfig('uuid'),
                this._getPaymentConfig('apiKey'),
                this.mwCardDivId,
                this._getPaymentConfig('payframeSrc'),
                this._getPaymentConfig('submitURL'),
                this.frameStyle,
                "Visa, Diners Club, Mastercard"
            );

            this.mwPayframe.mwCallback = (
                tokenStatus, payframeToken, payframeKey
            ) => this._payFrameCallback(
                tokenStatus, payframeToken, payframeKey
            );
            this.mwPayframe.loading = () => this._payFrameLoading();
            this.mwPayframe.loaded = () => this._payFrameLoaded();

            this.mwPayframe.deploy();
        },

        /**
         * Params:
         * - uuid - UserUUID
         * - apiKey - ApiKEY
         * - payFrameDivId - id of frame
         * - payframeSrc
         * - submitUrl
         * - iframeStyle
         * - acceptedCardTypesInput - with empty: only Visa and Mastercard will be accepted.
         * - methodInput - addCard | getPayframeToken, by default: getPayframeToken
         *
         * @type {payframe}
         */
        _initPayFrame: function (uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes) {
            return new payframe(uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes);
        },

        _payFrameCallback: function (tokenStatus, payframeToken, payframeKey) {
            if (tokenStatus === 'HAS_TOKEN') {

                // let postData = {
                //     'payframeToken': payframeToken,
                //     'payframeKey': payframeKey,
                //     'transactionAmount': '1.00',
                //     'transactionCurrency': 'AUD',
                //     'transactionProduct': 'Test Product',
                //     'customerName': 'Test Customer',
                //     'customerCountry': 'AU',
                //     'customerState': 'QLD',
                //     'customerCity': 'Brisbane',
                //     'customerAddress': '123 Test Street',
                //     'customerPostCode': '4000',
                //     'customerPhone': '61731665489',
                //     'customerEmail': 'mw@emailaddress.com',
                //     'customerIP': '1.1.1.1'
                // };

                let postData = {
                    payframeToken: payframeToken,
                    payframeKey: payframeKey,
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    billingAddress: quote.billingAddress()
                };

                $.when(
                    processCardAction(postData)
                ).fail(() => {
                    this.mwPayframe.reset();
                }).done((response) => {
                    this.mwPayframe.reset();
                    // var xmlDoc = $.parseXML(data);
                    // var responseCode = response.getElementsByTagName("responseCode")[0].childNodes[0].nodeValue;
                    // var responseMessage = response.getElementsByTagName("responseMessage")[0].childNodes[0].nodeValue;
                    // if(responseCode == 0 && responseMessage == 'Transaction approved') {
                    //     mwPayframe.reset();
                    // } else if(responseMessage == 'Transaction declined') {
                    //     console.log('Transaction Declined - Please enter a different card');
                    //     mwPayframe.reset();
                    // }

                });
            } else {
                if (this.mwPayframe.responseCode == -2 || this.mwPayframe.responseCode == -3) {
                    console.log('Validation failed - ' + this.mwPayframe.responseMessage);
                }
            }
        },

        _payFrameLoading: function () {

        },

        _payFrameLoaded: function () {

        },

        /**
         * Get config param
         *
         * @param key
         *
         * @return {*}
         * @private
         */
        _getPaymentConfig: function (key) {
            return window.checkoutConfig.payment.merchant_warrior_payframe[key];
        },

        /**
         * Returns payment method instructions.
         *
         * @return {*}
         */
        isActive: function () {
            return window.checkoutConfig.payment.merchant_warrior_payframe.enabled;
        },

        /**
         * Save order
         */
        placeOrder: function (data, event) {
            let placeOrder;

            if (event) {
                event.preventDefault();
            }

            if (this.validate()) {
                this.mwPayframe.submitPayframe();

                return ;

                placeOrder = placeOrderAction(this.getData());

                $.when(placeOrder).fail(() => {}).done(this.afterPlaceOrder.bind(this));

                return true;
            }
            return false;
        }
    });
});
