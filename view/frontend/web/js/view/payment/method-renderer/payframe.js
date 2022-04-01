define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'MerchantWarrior_Payment/js/action/place-order',
    'Magento_Checkout/js/model/full-screen-loader',
    'payframeLib'
], function ($, ko, _, Component, customerData, quote, priceUtils, placeOrderAction, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MerchantWarrior_Payment/payment/mw_payframe',
            mwIframeID: 'mwIframe',
            mwCardDivId: 'payframe-card-data',
            frameStyle: {
                errorTextColor: 'red',
                errorBorderColor: 'red',
                fontSize: '18px',
                width: '400px',
                cardTypeDisplay: 'right',
                padding: '5px',
                fieldHeight: '60px'
            },
            transactionResult: ''
        },
        mwPayframe: '',
        tdsCheck: '',
        payframeToken: '',
        payframeKey: '',
        method: 'getPayframeToken', // change this to getPayframeToken for payment payframe
        threeDS: false, // this will only work with the getPayframeToken method

        /**
         * Init component
         */
        initialize: function () {
            this._super();

            if (!this.isActive()) {
                return;
            }

            this.isChecked.subscribe(function(methodCode) {
                if (methodCode === this.item.method) {
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
            fullScreenLoader.startLoader();
            $('#' + this.mwCardDivId).html('');

            this.mwPayframe = this._initPayFrame(
                this._getPaymentConfig('uuid'),
                this._getPaymentConfig('apiKey'),
                this.mwCardDivId,
                this._getPaymentConfig('payframeSrc'),
                this._getPaymentConfig('submitURL'),
                this.frameStyle,
                this._getPaymentConfig('allowedTypeCards')
            );
            this.mwPayframe.mwCallback = (
                tokenStatus, payframeToken, payframeKey
            ) => this._payFrameCallback(
                tokenStatus, payframeToken, payframeKey
            );
            this.mwPayframe.loaded = () => this._payFrameLoaded();

            this.tdsCheck = this._initTdsCheck(
                this._getPaymentConfig('uuid'),
                this._getPaymentConfig('apiKey'),
                this.mwCardDivId,
                this._getPaymentConfig('submitURL'),
                {
                    width: '500px',
                    subFrame: true
                }
            );
            this.tdsCheck.mwCallback = (liabilityShifted, tdsToken) => this._tdsCallBack(liabilityShifted, tdsToken);

            this.mwPayframe.deploy();
        },

        /**
         * Init PayFrame function
         *
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
         * @return {payframe}
         * @private
         */
        _initPayFrame: function (uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes) {
            return new payframe(uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes);
        },

        /**
         * Init TDS Check function
         *
         * @param uuid
         * @param apiKey
         * @param tdsDivId
         * @param submitUrl
         * @param tstStyle
         *
         * @return {tdsCheck}
         * @private
         */
        _initTdsCheck: function (uuid, apiKey, tdsDivId, submitUrl, tstStyle) {
            return new tdsCheck(uuid, apiKey, tdsDivId, submitUrl, tstStyle);
        },

        /**
         * Init PayFrame CallBack function
         *
         * @param tokenStatus
         * @param payframeToken
         * @param payframeKey
         *
         * @private
         */
        _payFrameCallback: function (tokenStatus, payframeToken, payframeKey) {
            if (tokenStatus === 'HAS_TOKEN' && payframeToken && payframeKey) {
                this.payframeToken = payframeToken;
                this.payframeKey = payframeKey;

                // If you want the tdsCheck to use the same loading
                // or loaded functions as the mwPayframe, call link() on the tdsCheck object
                this.tdsCheck.link(this.mwPayframe);
                // When you have the payframeToken and payframeKey, call checkTDS,
                // passing in the payframeToken, payframeKey, transactionAmount, transactionCurrency
                // and transactionProduct
                this.tdsCheck.checkTDS(
                    this.payframeToken,
                    this.payframeKey,
                    this.getFormattedPrice(quote.totals().grand_total),
                    quote.totals().base_currency_code,
                    this.getItemsSku()
                );
            } else {
                if (this.mwPayframe.responseCode == -2 || this.mwPayframe.responseCode == -3) {
                    fullScreenLoader.stopLoader(true);
                }
            }
        },

        _payFrameLoaded: function () {
            fullScreenLoader.stopLoader(true);
        },

        /**
         * TDS Callback function
         *
         * @param liabilityShifted
         * @param tdsToken
         *
         * @private
         */
        _tdsCallBack: function (liabilityShifted, tdsToken) {
            if (liabilityShifted) {
                // If the bank has taken liability for the transaction,
                // submit the tdsToken with a processCard or processAuth transaction
                this.processCardAction(tdsToken);
            } else {
                if (
                    this.tdsCheck.mwTDSMessage === 'Cardholder not enrolled in 3DS'
                    && this.tdsCheck.mwTDSResult === ''
                ) {
                    this.processCardAction('');
                }

                if (this.tdsCheck.mwTDSResult === 'error') {
                    alert(this.tdsCheck.mwTDSMessage);
                }
                this.tdsCheck.destroy();
            }
        },

        /**
         * Get config param
         *
         * @param {string} key - parameter array key
         *
         * @return {*} - return string|int variables
         * @private
         */
        _getPaymentConfig: function (key) {
            return window.checkoutConfig.payment.merchant_warrior_payframe[key];
        },

        /**
         * Reset payment form
         *
         * @return {void}
         * @private
         */
        _resetForm: function () {
            this.payframeToken = '';
            this.payframeKey = '';

            this._initMwPayFrame();
        },

        /**
         * Process card action
         *
         * @param {string} tdsToken - 3DS token
         *
         * @return {void}
         */
        processCardAction: function (tdsToken) {
            this.transactionResult = {
                payframeToken: this.payframeToken,
                payframeKey: this.payframeKey,
                tdsToken: tdsToken,
                cartId: quote.getQuoteId(),
                email: quote.guestEmail
            };

            $.when(
                placeOrderAction(this.getData())
            ).fail(
                () => {
                    this.afterPlaceOrder.bind(this);
                }
            ).done(
                () => {
                    this.afterPlaceOrder.bind(this);
                }
            ).always(
                () => {
                    this._resetForm();
                }
            );
        },

        /**
         * Format price
         *
         * @param {*} price - price, ex: 10.15
         *
         * @return {*|String} - return formatted price, 10 -> 10.00
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(
                price,
                {
                    decimalSymbol: ".",
                    groupLength: 3,
                    groupSymbol: ",",
                    integerRequired: false,
                    pattern: "%s",
                    precision: 2,
                    requiredPrecision: 2
                }
            );
        },

        /**
         * Form items skus list
         *
         * @return {string}
         */
        getItemsSku: function () {
            let skus = '';
            _.each(quote.getItems(), (item) => {
                skus += item.sku + ', ';
            });
            return skus;
        },

        /**
         * Returns payment method instructions.
         *
         * @return {boolean} - is enabled
         */
        isActive: function () {
            if (window.checkoutConfig.payment.merchant_warrior_payframe
                && window.checkoutConfig.payment.merchant_warrior_payframe.active) {
                return true;
            }
            return false;
        },

        /**
         * Get payment data
         *
         * @return {{additional_data: {transaction_result: *}, method}}
         */
        getData: function() {
            return {
                'method': this.item.method,
                'additional_data': this.transactionResult
            };
        },

        /**
         * Save order
         */
        placeOrder: function (data, event) {
            if (event) {
                event.preventDefault();
            }

            if (this.validate()) {
                fullScreenLoader.startLoader();

                this.mwPayframe.submitPayframe();
            }
            return false;
        }
    });
});
