<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\ApiProcessInterface;

/**
 * Interface ProcessAuthInterface
 */
interface ProcessAuthInterface extends ApiProcessInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD = 'processAuth';
    /**#@-*/

    /**
     * Execute process Auth
     *
     * Expected params:
     * [
     * 'transactionAmount' => '1.00',
     * 'transactionCurrency' => 'AUD',
     * 'transactionProduct' => 'Test Product',
     * 'customerName' => 'Test Customer',
     * 'customerCountry' => 'AU',
     * 'customerState' => 'QLD',
     * 'customerCity' => 'Brisbane',
     * 'customerAddress' => '123 Test Street',
     * 'customerPostCode' => '4000',
     * 'customerPhone' => '61731665489',
     * 'customerEmail' => 'mw@emailaddress.com',
     * 'customerIP' => '1.1.1.1',
     * 'paymentCardName' => 'Test Customer',
     * 'paymentCardNumber' => '5123456789012346',
     * 'paymentCardExpiry' => '0521',
     * 'paymentCardCSC' => '123'
     * ]
     *
     * @param array $transactionParams
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $transactionParams): array;
}
