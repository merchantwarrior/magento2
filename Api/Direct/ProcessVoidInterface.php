<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\ApiProcessInterface;

/**
 * Interface ProcessVoidInterface
 */
interface ProcessVoidInterface extends ApiProcessInterface
{
    /**#@+
     * Api Method constants
     */
    public const API_METHOD = 'processVoid';
    /**#@-*/

    /**
     * Execute process void
     *
     * @param string $transactionId
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $transactionId): array;
}
