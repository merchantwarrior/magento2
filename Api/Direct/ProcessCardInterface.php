<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Direct;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ProcessCardInterface
 */
interface ProcessCardInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD = 'processCard';
    /**#@-*/

    /**
     * Execute process Card for Direct API
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
     * @param array $transactionParams
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $transactionParams): array;
}
