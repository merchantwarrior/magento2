<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Token;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Token\ProcessInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class Process extends RequestApi implements ProcessInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $method, array $transactionParams): array
    {
        if (!$this->config->isPayFrameActive()) {
            return [];
        }

        $transactionParams[self::METHOD] = $method;

        $this->validate($transactionParams);

        return $this->sendRequest($method, $transactionParams);
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

    }
}
