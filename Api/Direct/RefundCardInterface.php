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
     * @param array $transactionParams
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $transactionParams): array;
}
