<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Direct\ProcessInterface;
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

        $this->validate($method, $transactionParams);

        return $this->sendRequest($method, $transactionParams);
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
     * @param string $method
     * @param array $data
     *
     * @return void
     * @throws LocalizedException
     */
    private function validate(string $method, array $data): void
    {
        if ($method === ProcessInterface::API_METHOD_QUERY_DD) {
            $this->validateQueryDD($data);
        }
    }

    /**
     * Validate Query DD transaction
     *
     * @param array $data
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateQueryDD(array $data): void
    {
        if (!isset($data[self::TRANSACTION_ID])) {
            throw new LocalizedException(__('You must set Transaction ID!'));
        }
        if (!isset($data[self::TRANSACTION_REFERENCE_ID])) {
            throw new LocalizedException(__('You must set Transaction Reference ID!'));
        }
    }
}
