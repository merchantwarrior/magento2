define([
    'ko',
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'MerchantWarrior_Payment/js/action/place-order',
], function (
    ko,
    $,
    VaultComponent,
    quote,
    priceUtils,
    globalMessageList,
    additionalValidators,
    fullScreenLoader,
    placeOrderAction
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            active: false,
            template: 'MerchantWarrior_Payment/payment/cc/vault',
            mwCardDivId: 'payframe-vault-data',
            frameStyle: {
                errorTextColor: 'red',
                errorBorderColor: 'red',
                fontSize: '14px',
                width: '400px',
                cardTypeDisplay: 'right',
                padding: '5px',
                fieldHeight: '60px',
                placeholderStyle: {'fontSize': '14px'}
            },
            invalidClass: 'mw-hosted-fields-invalid',
        },
        tdsToken: '',
        cardID: '',
        cardKey: '',
        mwPayframe: '',
        isVaultShow: ko.observable(false),

        /**
         * Init component
         */
        initialize() {
            this._super();
        },

        /**
         * @returns {exports}
         */
        initObservable() {
            this._super().observe(['active']);
            return this;
        },

        /**
         * Is payment option active?
         *
         * @returns {boolean}
         */
        isActive() {
            let active = this.getId() === this.isChecked();
            this.active(active);
            return active;
        },

        /**
         * Return the payment method code.
         *
         * @returns {string}
         */
        getCode() {
            return 'merchant_warrior_cc_vault';
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard() {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate() {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType() {
            return this.details.type;
        },

        /**
         * Get show CVV Field
         * @returns {Boolean}
         */
        showCvvVerify() {
            return window.checkoutConfig.payment[this.code].cvvVerify;
        },

        /**
         * Get link to CC Logo
         *
         * @param cctype
         *
         * @return {*}
         */
        getIcon(cctype) {
            let type = cctype.toLocaleLowerCase(),
                icons = window.checkoutConfig.payment[this.code].icons;
            if(type == "mc") type = "mastercard";

            if (icons[type].url) {
                return icons[type].url;
            }
            return null;
        },

        /**
         * Show or hide the error message.
         *
         * @param selector
         *
         * @returns {boolean}
         */
        validateCvv(selector) {
            let $selector = $(selector);
            let value = $selector.val(),
                hostedElClass = '.hosted-control';

            $selector.parent(hostedElClass).removeClass(this.invalidClass);
            $selector.on('change', () => {
                $selector.parent(hostedElClass).removeClass(this.invalidClass);
            });

            if (value.length === 0) {
                $selector.parent(hostedElClass).addClass(this.invalidClass);
                return false;
            }

            if (this.getCardType() === 'amex' && value.length !== 4) {
                $selector.parent(hostedElClass).addClass(this.invalidClass);
                return false;
            }

            if (this.getCardType() !== 'amex' && value.length !== 3) {
                $selector.parent(hostedElClass).addClass(this.invalidClass);
                return false;
            }
            return true;
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
        _initTdsCheck(uuid, apiKey, tdsDivId, submitUrl, tstStyle) {
            var mwtdsCheckClass =  new tdsCheck(uuid, apiKey, tdsDivId, submitUrl, tstStyle);
            window.checkoutConfig.payment.mwtdsCheckClass = mwtdsCheckClass;
            return mwtdsCheckClass;
        },

        /**
         * Get payment data
         *
         * @return {{additional_data: {transaction_result: *}, method}}
         */
        getData() {
            return {
                'method': this.item.method,
                'additional_data': this._formTransactionResultData()
            };
        },
        /**
         * Callback function for PayFrameLoaded action
         *
         * @private
         */
        _payFrameLoaded() {
            this.isVaultShow(true);

            fullScreenLoader.stopLoader(true);
        },

        selectPaymentMethod (paymentMethod) {
            this._cleanGeneratedPayframe();
            return this._super(paymentMethod);
        },
        /**
         * Place order
         */
        placeOrder() {
            if (this.showCvvVerify()) {
                if (!this.validateCvv('#' + this.getId() + '_cc_cid') || !additionalValidators.validate()) {
                    return;
                }
            } else {
                if (!additionalValidators.validate()) {
                    return;
                }
            }
            $(".checkout").prop('disabled', true)
            
            fullScreenLoader.startLoader();
            document.getElementById(this.mwCardDivId + this.getId()).innerHTML = ''
            if (this._getPaymentConfig('is3dsEnabled')) {

                this.mwPayframe = this._initPayFrame(
                    this._getPaymentConfig('uuid'),
                    this._getPaymentConfig('apiKey'),
                    this.mwCardDivId + this.getId(),
                    this._getPaymentConfig('payframeSrc'),
                    this._getPaymentConfig('submitURL'),
                    this.frameStyle,
                    this._getPaymentConfig('allowedTypeCards'),

                );
                this.mwPayframe.loaded = () => this._payFrameLoaded();
                this.mwPayframe.deploy();
                let vaultClass = this;
                let payframeEvent = function () {
                    vaultClass.tdsCheck = vaultClass._initTdsCheck(
                        vaultClass._getPaymentConfig('uuid'),
                        vaultClass._getPaymentConfig('apiKey'),
                        vaultClass.mwCardDivId + vaultClass.getId(),
                        vaultClass._getPaymentConfig('submitURL'),
                        {
                            width: '500px',
                            subFrame: true
                        }
                    );
                    vaultClass.tdsCheck.mwCallback = (liabilityShifted, tdsToken) => vaultClass._tdsCallBack(liabilityShifted, tdsToken);
                    vaultClass.tdsCheck.link(vaultClass.mwPayframe);
                    var tdsAdditionalInfo = {tokenType : "vault"};

                    // When you have the payframeToken and payframeKey, call checkTDS,
                    // passing in the payframeToken, payframeKey, transactionAmount, transactionCurrency
                    // and transactionProduct
                    vaultClass.tdsCheck.checkTDS(
                        vaultClass.details.cardID,
                        vaultClass.details.cardKey,
                        vaultClass.getFormattedPrice(quote.totals().base_grand_total),
                        quote.totals().base_currency_code,
                        vaultClass.getItemsSku(),
                        tdsAdditionalInfo,
                    );

                }
                if(this.mwPayframe.mwIframe){
                    const checkLoaded = setInterval(function () {
                        if(vaultClass.mwPayframe.payframeTestLoaded){
                            clearInterval(checkLoaded);
                            payframeEvent();
                        }
                    }, 100);//Vault purchase need to check Parent-Iframe are fully loaded
                }

            } else {
                this.processCardAction();
            }

        },

        /**
         * Format price
         *
         * @param {*} price - price, ex: 10.15
         *
         * @return {*|String} - return formatted price, 10 -> 10.00
         */
        getFormattedPrice(price) {
            return priceUtils.formatPrice(
                price,
                {
                    decimalSymbol: ".",
                    groupLength: 0,
                    groupSymbol: "",
                    integerRequired: false,
                    pattern: "%s",
                    precision: 2,
                    requiredPrecision: 2
                }
            );
        },

        /**
         * TDS Callback function
         *
         * @param liabilityShifted
         * @param tdsToken
         *
         * @private
         */
        _tdsCallBack(liabilityShifted, tdsToken) {
            
            this.tdsToken = tdsToken;
            if (liabilityShifted) {
                // If the bank has taken liability for the transaction,
                // submit the tdsToken with a processCard or processAuth transaction
                this.processCardAction();
            } else {
                if (
                    this.tdsCheck.mwTDSMessage === 'Cardholder not enrolled in 3DS'
                    && this.tdsCheck.mwTDSResult === ''
                ) {
                    this.processCardAction();
                }

                if (
                    this.tdsCheck.mwTDSMessage === '3DS Failed'
                ) {
                    globalMessageList.addErrorMessage({
                        message: this.tdsCheck.mwTDSMessage
                    });
                }

                if (this.tdsCheck.mwTDSResult === 'error') {
                    globalMessageList.addErrorMessage({
                        message: this.tdsCheck.mwTDSMessage
                    });
                }
            }
            this.tdsCheck.destroy();
            this.tdsCheck.removeEventListener();
            this.mwPayframe.removeEventListener();
            this._resetForm();
            $(".checkout").prop('disabled', false);
        },

        /**
         * Process card action
         *
         * @return {void}
         */
        processCardAction() {
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
         * Form items skus list
         *
         * @return {string}
         */
        getItemsSku() {
            let skus = '';
            quote.getItems().forEach((item) => {
                skus += item.sku + ', ';
            });
            return skus;
        },

        /**
         * Get config param
         *
         * @param {string} key - parameter array key
         *
         * @return {*} - return string|int variables
         * @private
         */
        _getPaymentConfig(key) {
            return window.checkoutConfig.payment.merchant_warrior_payframe[key];
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
         * - methodInput - addCard | getPayframeToken | tokenTDS, by default: getPayframeToken
         *
         * @return {payframe}
         * @private
         */
        _initPayFrame(uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes) {
            var mwPayframeClass = new payframe(uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes, "tokenTDS");
            window.checkoutConfig.payment.mwPayframeClass = mwPayframeClass;
            return mwPayframeClass;
        },

        /**
         * Reset payment form
         *
         * @return {void}
         * @private
         */
        _resetForm() {
            fullScreenLoader.stopLoader(true);
            $('#' + this.getId() + '_cc_cid').val('');
        },

        /**
         * Form and return transaction data
         *
         * @return {{payframeKey: *, cartId: *, payframeToken: *, tdsToken: *, email}}
         *
         * @private
         */
        _formTransactionResultData() {
            let transactionResult = {
                cartId: quote.getQuoteId(),
                email: quote.guestEmail,
                public_hash: this.publicHash,
                tdsToken: this.tdsToken,
            };

            if (this.showCvvVerify()) {
                transactionResult.paymentCardCSC = $('#' + this.getId() + '_cc_cid').val();
            }
            return transactionResult;
        },

        /**
         * clean up generated payment form
         *
         * @return {void}
         * @private
         */
        _cleanGeneratedPayframe(){
            var payframeDiv = null;
            payframeDiv = document.getElementsByClassName("payframe-card-div");
            if(payframeDiv)for (let i = 0; i < payframeDiv.length; i++) {
                if(payframeDiv[i].innerHTML){
                    payframeDiv[i].innerHTML = "";
                }
            }
            if(window.checkoutConfig.payment.mwPayframeClass)window.checkoutConfig.payment.mwPayframeClass.removeEventListener();
            if(window.checkoutConfig.payment.mwtdsCheckClass)window.checkoutConfig.payment.mwtdsCheckClass.removeEventListener();
            $(".checkout").prop('disabled', false);
        }
    });
});
