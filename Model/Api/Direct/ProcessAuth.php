<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Direct\ProcessAuthInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class ProcessAuth extends RequestApi implements ProcessAuthInterface
{
    /**
     * @inheritdoc
     */
    public function execute(array $transactionParams): array
    {
        if (!$this->config->isEnabled()) {
            return [];
        }

        $this->validate($transactionParams);

        $transactionParams[self::METHOD] = self::API_METHOD;

        return $this->sendRequest(self::API_METHOD, $transactionParams);
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
     * @param array $data
     *
     * @return void
     * @throws LocalizedException
     */
    private function validate(array $data): void
    {
        if (empty($data[self::PAYMENT_CARD_NUMBER])) {
            throw new LocalizedException(__('You must enter card number!'));
        }

        if (empty($data[self::PAYMENT_CARD_CSC])) {
            throw new LocalizedException(__('You must enter card CSC code!'));
        }

        if (empty($data[self::PAYMENT_CARD_EXPIRY])) {
            throw new LocalizedException(__('You must enter card expiry date!'));
        }

        if (empty($data[self::PAYMENT_CARD_NAME])) {
            throw new LocalizedException(__('You must enter card name!'));
        }

        if (empty($data[self::TRANSACTION_AMOUNT])) {
            throw new LocalizedException(__('You must enter correct transaction data!'));
        }

        if (empty($data[self::TRANSACTION_CURRENCY])) {
            throw new LocalizedException(__('You must enter correct transaction data!'));
        }
    }
}
