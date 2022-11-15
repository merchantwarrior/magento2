<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Token;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\ApiProcessInterface;

/**
 * Interface ProcessInterface
 */
interface ProcessInterface extends ApiProcessInterface
{
    /**#@+
     * Api Method constants
     */
    public const API_METHOD_PROCESS_CARD = 'processCard';
    public const API_METHOD_PROCESS_AUTH = 'processAuth';
    /**#@-*/

    /**
     * Execute process for token
     *
     * Expected params:
     * [
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
     *  'cardID' => ''
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
