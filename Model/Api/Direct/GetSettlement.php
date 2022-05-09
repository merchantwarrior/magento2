<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use MerchantWarrior\Payment\Api\Direct\GetSettlementInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class GetSettlement extends RequestApi implements GetSettlementInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $settlementFrom, string $settlementTo): ?string
    {
        if (!$this->config->isEnabled()) {
            return '';
        }

        $result = $this->sendRequest(
            self::API_METHOD,
            [
                self::METHOD => self::API_METHOD,
                self::SETTLEMENT_FROM => $settlementFrom,
                self::SETTLEMENT_TO => $settlementTo
            ]
        );

        return $this->saveToZipData->execute($settlementFrom . '-' . $settlementTo, $result['mwResponse']);
    }

    /**
     * Get base API url
     *
     * @return string
     */
    protected function getApiUrl(): string
    {
        return $this->config->getApiUrl() . 'post/';
    }
}
