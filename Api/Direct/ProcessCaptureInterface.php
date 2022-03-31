<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Direct;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ProcessCaptureInterface
 */
interface ProcessCaptureInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD = 'processCapture';
    /**#@-*/

    /**
     * Execute process capture
     *
     * @param array $transactionParams
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $transactionParams): array;
}
