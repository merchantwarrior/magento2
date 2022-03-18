define([
    'Magento_Checkout/js/view/payment/default',
    'payframeLib'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MerchantWarrior_Payment/payment/mw_payframe',
            mwIframeID: 'mwIframe',
            mwCardDivId: 'payframe-card-data'
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

        _initMwPayFrame: function () {
            let style = {
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
                fieldHeight: '60px',
            };

            // this._getPaymentConfig('src')

            this.mwPayframe = new payframe(
                this._getPaymentConfig('uuid'),
                this._getPaymentConfig('apiKey'),
                this.mwCardDivId,
                'camp',
                this._getPaymentConfig('submitURL'),
                style,
                "Visa, Diners Club, Mastercard"
            );

            this.mwPayframe.mwCallback = function() {
                //Example of success and error scenarios below
                if (this.threeDS) {
                    processResponseEvent(method, arguments, tdsCheck);
                } else {
                    console.log('Failed to get payframeToken');
                    if (this.mwPayframe.responseCode == -2 || this.mwPayframe.responseCode == -3) {
                        console.log('Validation failed - ' + this.mwPayframe.responseMessage);
                    }
                    //processResponseEvent(method, arguments);
                }
            }.bind(this);

            this.mwPayframe.loading = function() {
                // let mwIframe = document.getElementById(this.mwIframeID),
                //     cardDiv = document.getElementById(this.mwCardDivId);

                // Hide the payframe during load operations
                // mwIframe.style.visibility = 'hidden';

                // Assign the parent div the same dimensions as the payframe
                // let height = mwIframe.height,
                //     width = mwIframe.width;
                //
                // cardDiv.style.height = height;
                // cardDiv.style.width = width;
                //
                // // Place a loading animation in the center of the payframe
                // cardDiv.style.background = "url('https://secure.merchantwarrior.com/inc/image/loading_gif.gif') center center no-repeat";
            }.bind(this);

            this.mwPayframe.loaded = function() {
                // button.style.visibility = "visible";
                // let mwIframe = document.getElementById(this.mwIframeID),
                //     cardDiv = document.getElementById(this.mwCardDivId);
                // // Remove the loading animation after the payframe has completed its operations and display the payframe
                // cardDiv.style.background = 'none';
                // if (mwIframe) {
                //     mwIframe.style.visibility = 'visible';
                // }
            }.bind(this);

            this.mwPayframe.deploy();
        },

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
        }
    });
});
