<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Token;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface AddCardInterface
 */
interface AddCardInterface
{
    /**#@+
     * Api Method constants
     */
    public const API_METHOD = 'addCard';
    /**#@-*/

    /**
     * Add Card
     *
     * Expected params:
     * [
     *  'cardName' => '',
     *  'cardNumber' => '',
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
