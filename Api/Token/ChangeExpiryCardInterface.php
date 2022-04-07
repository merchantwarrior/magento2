<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Token;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ChangeExpiryCardInterface
 */
interface ChangeExpiryCardInterface
{
    /**#@+
     * Api Method constants
     */
    public const API_METHOD = 'changeExpiry';
    /**#@-*/

    /**
     * Add Card
     *
     * Expected params:
     * [
     *  'cardID' => '',
     *  'cardExpiryMonth' => '',
     *  'cardExpiryYear' => ''
     * ]
     *
     * @param array $transactionParams
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $transactionParams): array;
}
