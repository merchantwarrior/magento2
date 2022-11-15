<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Token;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\ApiProcessInterface;

/**
 * Interface AddCardInterface
 */
interface AddCardInterface extends ApiProcessInterface
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
