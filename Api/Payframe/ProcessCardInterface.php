<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Payframe;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ProcessAuthInterface
 */
interface ProcessCardInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD = 'processCard';
    /**#@-*/

    /**
     * Execute process Card for Payframe
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
