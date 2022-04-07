<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Token;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Token\RemoveCardInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class RemoveCard extends RequestApi implements RemoveCardInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $cardID): array
    {
        if (!$this->config->isPayFrameActive()) {
            return [];
        }

        $transactionParams[self::METHOD] = RemoveCardInterface::API_METHOD;
        $transactionParams['cardID'] = $cardID;

        $this->validate($transactionParams);

        return $this->sendRequest(RemoveCardInterface::API_METHOD, $transactionParams);
    }

    /**
     * Get base API url
     *
     * @return string
     */
    protected function getApiUrl(): string
    {
        return $this->config->getApiUrl() . 'token/';
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
        if (!isset($data['cardName'])) {
            throw new LocalizedException(__('Your card data is incorrect!'));
        }
        if (!isset($data['cardNumber'])) {
            throw new LocalizedException(__('Your card data is incorrect!'));
        }
        if (!isset($data['cardExpiryMonth'])) {
            throw new LocalizedException(__('Your card data is incorrect!'));
        }
        if (!isset($data['cardExpiryYear'])) {
            throw new LocalizedException(__('Your card data is incorrect!'));
        }
    }
}
