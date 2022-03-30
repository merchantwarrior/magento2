<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class ProcessVoid extends RequestApi implements ProcessVoidInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $transactionId): array
    {
        if (!$this->config->isEnabled()) {
            return [];
        }

        return $this->sendRequest(
            self::API_METHOD,
            [
                self::METHOD => self::API_METHOD,
                self::TRANSACTION_ID => $transactionId
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
        // TODO: Add additional validation
    }
}
