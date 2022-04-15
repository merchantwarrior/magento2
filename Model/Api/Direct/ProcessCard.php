<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Direct\ProcessCardInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class ProcessCard extends RequestApi implements ProcessCardInterface
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
        if (!isset($data[self::PAYMENT_CARD_TYPE])) {
            throw new LocalizedException(__('You must select card type!'));
        }

        if (!in_array($data[self::PAYMENT_CARD_TYPE], $this->config->getAdminAllowedTypeCards())) {
            throw new LocalizedException(__('This card type is not allowed!'));
        }

        if (!isset($data[self::PAYMENT_CARD_NUMBER])) {
            throw new LocalizedException(__('You must enter card number!'));
        }

        if (!isset($data[self::PAYMENT_CARD_CSC])) {
            throw new LocalizedException(__('You must enter card CSC code!'));
        }

        if (!isset($data[self::PAYMENT_CARD_EXPIRY])) {
            throw new LocalizedException(__('You must enter card expiry date!'));
        }

        if (!isset($data[self::PAYMENT_CARD_NAME])) {
            throw new LocalizedException(__('You must enter card name!'));
        }
    }
}
