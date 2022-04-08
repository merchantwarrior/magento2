<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Token;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Token\AddCardInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class AddCard extends RequestApi implements AddCardInterface
{
    /**
     * @inheritdoc
     */
    public function execute(array $transactionParams): array
    {
        if (!$this->config->isPayFrameActive()) {
            return [];
        }

        $transactionParams[self::METHOD] = AddCardInterface::API_METHOD;

        $this->validate($transactionParams);

        return $this->sendRequest(AddCardInterface::API_METHOD, $transactionParams);
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
        if (!isset($data['cardID'])) {
            throw new LocalizedException(__('Your card ID is incorrect!'));
        }
    }
}
