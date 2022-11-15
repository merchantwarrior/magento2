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
            template: 'MerchantWarrior_Payment/payment/cc/vault',
            invalidClass: 'mw-hosted-fields-invalid',
        },

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
            };

            if (this.showCvvVerify()) {
                transactionResult.paymentCardCSC = $('#' + this.getId() + '_cc_cid').val();
            }
            return transactionResult;
        },
    });
});
