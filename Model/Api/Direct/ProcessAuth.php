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

        return $this->sendRequest($transactionParams);
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
     * Set request
     *
     * @param array $data
     *
     * @return array
     * @throws LocalizedException
     */
    private function sendRequest(array $data): array
    {
        $data = $this->formData($data);

        $this->sendPostRequest(self::API_METHOD,  $data);

        if ($this->getResponseCode(self::API_METHOD) !== '0') {
            throw new LocalizedException(__($this->getResponseMessage(self::API_METHOD)));
        }
        return $this->getResponse(self::API_METHOD)->toArray();
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
