define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'MerchantWarrior_Payment/js/action/place-order',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Ui/js/model/messageList'
], function (
    $,
    ko,
    Component,
    quote,
    priceUtils,
    placeOrderAction,
    fullScreenLoader,
    VaultEnabler,
    globalMessageList
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MerchantWarrior_Payment/payment/mw_payframe',
            mwIframeID: 'mwIframe',
            mwCardDivId: 'payframe-card-data',
            frameStyle: {
                errorTextColor: 'red',
                errorBorderColor: 'red',
                fontSize: '14px',
                width: '400px',
                cardTypeDisplay: 'right',
                padding: '5px',
                fieldHeight: '60px',
                placeholderStyle: {'fontSize': '14px'}
            }
        },
        mwPayframe: '',
        payframeToken: '',
        payframeKey: '',
        tdsToken: '',
        method: 'getPayframeToken', // change this to getPayframeToken for payment payframe,
        isVaultShow: ko.observable(false),

        /**
         * Init component
         */
        initialize() {
            this._super();
            if (!this.isActive()) {
                return;
            }

            this._cleanGeneratedPayframe();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            this.isChecked.subscribe((methodCode) => {
                if (methodCode === this.item.method) {
                    this._initMwPayFrame();
                }
            });
        },

        /**
         * After render action
         */
        initForm() {
            if (this.isChecked() === 'merchant_warrior_payframe') {
                this._initMwPayFrame();
            }
        },

        selectPaymentMethod (paymentMethod) {
            this._cleanGeneratedPayframe();
            this._resetForm();
            return this._super(paymentMethod);
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
         * Returns payment method instructions.
         *
         * @return {boolean} - is enabled
         */
        isActive() {
            return !!(window.checkoutConfig.payment.merchant_warrior_payframe && this._getPaymentConfig('active'));
        },

        /**
         * Returns vault code.
         *
         * @returns {String}
         */
        getVaultCode() {
            return this._getPaymentConfig('ccVaultCode');
        },

        /**
         * Check is vault enabled
         *
         * @returns {Boolean}
         */
        isVaultEnabled() {
            return this.vaultEnabler.isVaultEnabled();
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
         * Save order
         */
        placeOrder(data, event) {
            if (event) {
                event.preventDefault();
            }

            if (this.validate()) {
                fullScreenLoader.startLoader();

                this.mwPayframe.submitPayframe();
            }
            return false;
        },

        /**
         * Form ID which response of saving CC to vault
         *
         * @return {string}
         */
        getSaveToVaultId() {
            return this.getCode() + '_enable_vault';
        },

        /**
         * Form and return transaction data
         *
         * @return {{payframeKey: *, cartId: *, payframeToken: *, tdsToken: *, email}}
         * @private
         */
        _formTransactionResultData() {
            let transactionResult = {
                payframeToken: this.payframeToken,
                payframeKey: this.payframeKey,
                tdsToken: this.tdsToken,
                cartId: quote.getQuoteId(),
                email: quote.guestEmail
            };

            if (this._isSaveToVaultEnabled()) {
                transactionResult.addCard = '1';
                transactionResult.is_active_payment_token_enabler = true;
            }
            return transactionResult;
        },

        /**
         * Check is the saving to vault checkbox is checked
         *
         * @return {boolean}
         * @private
         */
        _isSaveToVaultEnabled() {
            if (!this.isVaultEnabled()) {
                return false;
            }

            const isSaveToVault = document.getElementById(this.getSaveToVaultId());
            if (typeof isSaveToVault !== "undefined" && isSaveToVault !== null) {
                return isSaveToVault.checked;
            }
            return false;
        },

        /**
         * Initialize Pay Frame object
         *
         * @private
         */
        _initMwPayFrame() {
            fullScreenLoader.startLoader();
            this._cleanGeneratedPayframe();

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
        _initPayFrame(uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes) {
            var mwPayframeClass = new payframe(uuid, apiKey, payFrameDivId, payframeSrc, submitUrl, iframeStyle, acceptedCardTypes);
            window.checkoutConfig.payment.mwPayframeClass = mwPayframeClass;
            return mwPayframeClass;
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
         * Init PayFrame CallBack function
         *
         * @param tokenStatus
         * @param payframeToken
         * @param payframeKey
         *
         * @private
         */
        _payFrameCallback(tokenStatus, payframeToken, payframeKey) {

            if (tokenStatus === 'HAS_TOKEN' && payframeToken && payframeKey) {
                this.payframeToken = payframeToken;
                this.payframeKey = payframeKey;

                if (this._getPaymentConfig('is3dsEnabled')) {

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
                    this.processCardAction();
                }
            } else {
                if (this.mwPayframe.responseCode === -2 || this.mwPayframe.responseCode === -3) {
                    fullScreenLoader.stopLoader(true);
                }
            }
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
                    && this.tdsCheck.mwTDSResult === 'U'
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
         * Reset payment form
         *
         * @return {void}
         * @private
         */
        _resetForm() {
            this.payframeToken = '';
            this.payframeKey = '';
            this.tdsToken = '';

            this._initMwPayFrame();

            fullScreenLoader.stopLoader(true);
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
