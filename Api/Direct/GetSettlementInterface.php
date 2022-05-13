<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\ApiProcessInterface;

/**
 * Interface GetSettlementInterface
 */
interface GetSettlementInterface extends ApiProcessInterface
{
    /**#@+
     * Api Method constants
     */
    const API_METHOD = 'getSettlement';
    /**#@-*/

    /**#@+
     * Date format
     */
    const DATE_FORMAT = 'Y-m-d';
    /**#@-*/

    /**
     * Execute refund card
     * Expects dates from and to in format: Y-m-d ( 2020-01-01 )
     *
     * @param string $settlementFrom
     * @param string $settlementTo
     *
     * @return null|string
     * @throws LocalizedException
     */
    public function execute(string $settlementFrom, string $settlementTo): ?string;
}
