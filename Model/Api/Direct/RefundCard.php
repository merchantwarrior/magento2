<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Direct\RefundCardInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class RefundCard extends RequestApi implements RefundCardInterface
{
    /**
     * @inheritdoc
     */
    public function execute(
        string $transactionAmount,
        string $currency,
        string $transactionId,
        string $refundAmount
    ): array {
        if (!$this->config->isEnabled()) {
            return [];
        }

        return $this->sendRequest(
            self::API_METHOD,
            [
                self::METHOD => self::API_METHOD,
                self::TRANSACTION_AMOUNT => $transactionAmount,
                self::TRANSACTION_CURRENCY => $currency,
                self::TRANSACTION_ID => $transactionId,
                self::REFUND_AMOUNT => $refundAmount
            ]
        );
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
     * @param string $date
     *
     * @return void
     * @throws LocalizedException
     */
    private function validate(string $date): void
    {
        // TODO: Add additonal validation for data
    }
}
