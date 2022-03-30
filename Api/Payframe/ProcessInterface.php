<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Payframe;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ProcessInterface
 */
interface ProcessInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD_AUTH = 'processAuth';
    const API_METHOD_CARD = 'processCard';
    /**#@-*/

    /**
     * Execute process for Payframe
     *
     * Expected params:
     * [
     *  'method' => '',
     *  'merchantUUID' => '5265',
     *  'apiKey' => '',
     *  'transactionAmount' => '',
     *  'transactionCurrency' => '',
     *  'transactionProduct' => '',
     *  'customerName' => '',
     *  'customerCountry' => '',
     *  'customerState' => '',
     *  'customerCity' => '',
     *  'customerAddress' => '',
     *  'customerPostCode' => '',
     *  'customerPhone' => '',
     *  'customerEmail' => '',
     *  'customerIP' => '',
     *  'payframeToken' => '',
     *  'payframeKey' => '',
     *  'hash' => ''
     * ]
     *
     * @param string $method
     * @param array $transactionParams
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $method, array $transactionParams): array;
}
