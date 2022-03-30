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
        if (!isset($data[self::PAYFRAME_KEY])) {
            throw new LocalizedException(__('Your card is incorrect!'));
        }

        if (!isset($data[self::PAYFRAME_TOKEN])) {
            throw new LocalizedException(__('Your card is incorrect!'));
        }
    }
}
