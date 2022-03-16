<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api;

/**
 * Interface ProcessVoidInterface
 */
interface ProcessVoidInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD = 'processVoid';
    /**#@-*/

    /**
     * Execute process void
     *
     * @param string $transactionId
     *
     * @return array
     */
    public function execute(string $transactionId): array;
}
