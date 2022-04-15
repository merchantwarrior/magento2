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
    public const MERCHANT_USER_ID = 'merchantUUID';
    public const API_KEY = 'apiKey';
    /**#@-*/

    /**#@+
     * Post params
     */
    public const METHOD = 'method';
    /**#@-*/

    /**#@+
     * Api Direct Method constants
     */
    public const TRANSACTION_AMOUNT = 'transactionAmount';
    public const TRANSACTION_CURRENCY = 'transactionCurrency';
    public const TRANSACTION_PRODUCT = 'transactionProduct';
    public const CUSTOMER_NAME = 'customerName';
    public const CUSTOMER_COUNTRY = 'customerCountry';
    public const CUSTOMER_STATE = 'customerState';
    public const CUSTOMER_CITY = 'customerCity';
    public const CUSTOMER_ADDRESS = 'customerAddress';
    public const CUSTOMER_POST_CODE = 'customerPostCode';
    public const CUSTOMER_PHONE = 'customerPhone';
    public const CUSTOMER_EMAIL = 'customerEmail';
    public const CUSTOMER_IP = 'customerIP';
    public const PAYMENT_CARD_NAME = 'paymentCardName';
    public const PAYMENT_CARD_NUMBER = 'paymentCardNumber';
    public const PAYMENT_CARD_EXPIRY = 'paymentCardExpiry';
    public const PAYMENT_CARD_CSC = 'paymentCardCSC';
    public const REFUND_AMOUNT = 'refundAmount';
    public const CAPTURE_AMOUNT = 'captureAmount';
    public const TRANSACTION_ID = 'transactionID';
    public const TRANSACTION_REFERENCE_ID = 'transactionReferenceID';
    /**#@-*/

    /**#@+
     * Api Payframe Method constants
     */
    public const PAYFRAME_KEY = 'payframeKey';
    public const PAYFRAME_TOKEN = 'payframeToken';
    public const PAYFRAME_THREE_DS_TOKEN = 'threeDSToken';
    /**#@-*/

    /**#@+
     * Additional Api Payframe Method constants
     */
    public const PAYFRAME_ADD_TO_CARD_KEY = 'addCard';
    /**#@-*/
}
