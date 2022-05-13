<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
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

        $this->validate($settlementFrom, $settlementTo);

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

    /**
     * Validate
     *
     * @param string $settlementFrom
     * @param string $settlementTo
     *
     * @return void
     * @throws LocalizedException
     */
    private function validate(string $settlementFrom, string $settlementTo): void
    {
        $date = \DateTime::createFromFormat(GetSettlementInterface::DATE_FORMAT, $settlementFrom)
            ->format(GetSettlementInterface::DATE_FORMAT);
        if ($date !== $settlementFrom) {
            throw new LocalizedException(__('Settlement From date must be in Y-m-d format!'));
        }

        $date = \DateTime::createFromFormat(GetSettlementInterface::DATE_FORMAT, $settlementTo)
            ->format(GetSettlementInterface::DATE_FORMAT);
        if ($date !== $settlementTo) {
            throw new LocalizedException(__('Settlement To date must be in Y-m-d format!'));
        }
    }
}
