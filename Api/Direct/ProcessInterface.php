<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Direct;

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
    public const API_METHOD_QUERY_DD = 'queryDD';
    public const API_METHOD_QUERY_CARD = 'queryCard';
    /**#@-*/

    /**
     * Execute process for Payframe
     *
     * Expected params:
     * [
     *  'method' => '',
     *  'merchantUUID' => '',
     *  'apiKey' => '',
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
