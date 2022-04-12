define([
    'ko',
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'MerchantWarrior_Payment/js/action/place-order',
    'mage/url'
], function (
    ko,
    $,
    VaultComponent,
    quote,
    globalMessageList,
    additionalValidators,
    fullScreenLoader,
    placeOrderAction,
    url
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            active: false,
            hostedFieldsInstance: null,
            template: 'MerchantWarrior_Payment/payment/cc/vault',
            vaultedCVV: ko.observable(""),
            isValidCvv: false,
            icon: '',
            onInstanceReady: function (instance) {
                instance.on('validityChange', this.onValidityChange.bind(this));
            }
        },

        /**
         * Event fired by Braintree SDK whenever input value length matches the validation length.
         * In the case of a CVV, this is 3, or 4 for AMEX.
         * @param event
         */
        onValidityChange: function (event) {
            if (event.emittedBy === 'cvv') {
                this.isValidCvv = event.fields.cvv.isValid;
            }
        },

        /**
         * @returns {exports}
         */
        initObservable: function () {
            this._super().observe(['active']);
            return this;
        },

        /**
         * Is payment option active?
         *
         * @returns {boolean}
         */
        isActive: function () {
            let active = this.getId() === this.isChecked();
            this.active(active);
            return active;
        },

        /**
         * Fired whenever a payment option is changed.
         * @param isActive
         */
        onActiveChange: function (isActive) {
            var self = this;

            if (!isActive) {
                return;
            }

            if (self.showCvvVerify()) {

            }
        },

        /**
         * Initialize the CVV input field with the Hosted Fields SDK.
         */
        initHostedCvvField: function () {

        },

        /**
         * Return the payment method code.
         *
         * @returns {string}
         */
        getCode: function () {
            return 'merchant_warrior_cc_vault';
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        /**
         * Get show CVV Field
         * @returns {Boolean}
         */
        showCvvVerify: function () {
            return window.checkoutConfig.payment[this.code].cvvVerify;
        },

        /**
         * Get link to CC Logo
         *
         * @param cctype
         *
         * @return {*}
         */
        getIcon: function (cctype) {
            let type = cctype.toLocaleLowerCase(),
                icons = window.checkoutConfig.payment[this.code].icons;

            if (icons[type].url) {
                return icons[type].url;
            }
            return null;
        },

        /**
         * Show or hide the error message.
         *
         * @param selector
         * @param state
         *
         * @returns {boolean}
         */
        validateCvv: function (selector, state) {
            let $selector = $(selector),
                invalidClass = 'mw-hosted-fields-invalid';

            if (state === true) {
                $selector.removeClass(invalidClass);
                return true;
            }

            $selector.addClass(invalidClass);
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
                'additional_data': this._formTransactionResultData()
            };
        },

        /**
         * Place order
         */
        placeOrder: function () {
            let self = this;

            if (self.showCvvVerify()) {
                if (!self.validateCvv('#' + self.getId() + '_cid', self.isValidCvv)
                    || !additionalValidators.validate()
                ) {
                    return;
                }
            } else {
                if (!additionalValidators.validate()) {
                    return;
                }
            }

            fullScreenLoader.startLoader();

            if (self.showCvvVerify()) {
                fullScreenLoader.stopLoader();
                globalMessageList.addErrorMessage({
                    message: 'CVV verification failed.'
                });
            } else {
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
                );
            }
        },

        /**
         * Form and return transaction data
         *
         * @return {{payframeKey: *, cartId: *, payframeToken: *, tdsToken: *, email}}
         *
         * @private
         */
        _formTransactionResultData: function () {
            return {
                cartId: quote.getQuoteId(),
                email: quote.guestEmail,
                public_hash: this.publicHash
            };
        },
    });
});
