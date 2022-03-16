<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api;

/**
 * Interface RefundCardInterface
 */
interface RefundCardInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD = 'refundCard';
    /**#@-*/

    /**
     * Execute refund card
     *
     * @param float $transactionAmount
     * @param string $currency
     * @param string $transactionId
     * @param float $refundAmount
     *
     * @return array
     */
    public function execute(
        float $transactionAmount,
        string $currency,
        string $transactionId,
        float $refundAmount
    ): array;
}
