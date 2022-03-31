<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api;

/**
 * Interface RefundCardInterface
 */
interface RequestApiInterface
{
    /**#@+
     * Authorization keys
     */
    const MERCHANT_USER_ID = 'merchantUUID';
    const API_KEY = 'apiKey';
    /**#@-*/

    /**#@+
     * Post params
     */
    const METHOD = 'method';
    /**#@-*/

    /**#@+
     * Api Direct Method constants
     */
    const TRANSACTION_AMOUNT = 'transactionAmount';
    const TRANSACTION_CURRENCY = 'transactionCurrency';
    const TRANSACTION_PRODUCT = 'transactionProduct';
    const CUSTOMER_NAME = 'customerName';
    const CUSTOMER_COUNTRY = 'customerCountry';
    const CUSTOMER_STATE = 'customerState';
    const CUSTOMER_CITY = 'customerCity';
    const CUSTOMER_ADDRESS = 'customerAddress';
    const CUSTOMER_POST_CODE = 'customerPostCode';
    const CUSTOMER_PHONE = 'customerPhone';
    const CUSTOMER_EMAIL = 'customerEmail';
    const CUSTOMER_IP = 'customerIP';
    const PAYMENT_CARD_NAME = 'paymentCardName';
    const PAYMENT_CARD_NUMBER = 'paymentCardNumber';
    const PAYMENT_CARD_EXPIRY = 'paymentCardExpiry';
    const PAYMENT_CARD_CSC = 'paymentCardCSC';
    const TRANSACTION_ID = 'transactionID';
    const REFUND_AMOUNT = 'refundAmount';
    const CAPTURE_AMOUNT = 'captureAmount';
    /**#@-*/

    /**#@+
     * Api Payframe Method constants
     */
    const PAYFRAME_KEY = 'payframeKey';
    const PAYFRAME_TOKEN = 'payframeToken';
    const PAYFRAME_THREE_DS_TOKEN = 'threeDSToken';
    /**#@-*/
}
