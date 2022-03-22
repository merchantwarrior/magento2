<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Direct;

use Magento\Framework\Exception\LocalizedException;

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
     * @param string $transactionAmount
     * @param string $currency
     * @param string $transactionId
     * @param string $refundAmount
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(
        string $transactionAmount,
        string $currency,
        string $transactionId,
        string $refundAmount
    ): array;
}
