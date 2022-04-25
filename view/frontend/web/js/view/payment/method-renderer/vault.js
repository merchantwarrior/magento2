define([
    'ko',
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'MerchantWarrior_Payment/js/action/place-order',
], function (
    ko,
    $,
    VaultComponent,
    quote,
    globalMessageList,
    additionalValidators,
    fullScreenLoader,
    placeOrderAction
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            active: false,
            hostedFieldsInstance: null,
            template: 'MerchantWarrior_Payment/payment/cc/vault',
            vaultedCVV: ko.observable(""),
            isValidCvv: false,
            invalidClass: 'mw-hosted-fields-invalid',
            icon: ''
        },

        /**
         * Init component
         */
        initialize: function () {
            this._super();
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
        validateCvv: function (selector) {
            let $selector = $(selector);
            let value = $selector.val();

            $selector.parent('.hosted-control').removeClass(this.invalidClass);
            $selector.on('change', () => {
                $selector.parent('.hosted-control').removeClass(this.invalidClass);
            });

            if (value.length === 0) {
                $selector.parent('.hosted-control').addClass(this.invalidClass);
                return false;
            }

            if (this.getCardType() === 'amex' && value.length !== 4) {
                $selector.parent('.hosted-control').addClass(this.invalidClass);
                return false;
            }

            if (this.getCardType() !== 'amex' && value.length !== 3) {
                $selector.parent('.hosted-control').addClass(this.invalidClass);
                return false;
            }
            return true;
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
                if (!self.validateCvv('#' + self.getId() + '_cc_cid')
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
                   this._resetForm()
                }
            );
        },

        /**
         * Reset payment form
         *
         * @return {void}
         * @private
         */
        _resetForm: function () {
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
        _formTransactionResultData: function () {
            let transactionResult = {
                cartId: quote.getQuoteId(),
                email: quote.guestEmail,
                public_hash: this.publicHash,
            };

            if (this.showCvvVerify()) {
                transactionResult.paymentCardCSC = $('#' + this.getId() + '_cc_cid').val();
            }
            return transactionResult;
        },
    });
});
