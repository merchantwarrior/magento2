<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Token;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\ApiProcessInterface;

/**
 * Interface RemoveCardInterface
 */
interface RemoveCardInterface extends ApiProcessInterface
{
    /**#@+
     * Api Method constants
     */
    public const API_METHOD = 'removeCard';
    /**#@-*/

    /**
     * Remove Card
     *
     * @param string $cardID
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $cardID): array;
}
