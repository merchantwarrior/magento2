<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Payframe;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Payframe\ProcessInterface;
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
        return $this->config->getApiUrl() . 'payframe/';
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
        if (empty($data[self::PAYFRAME_KEY])) {
            throw new LocalizedException(__('Your card is incorrect!'));
        }

        if (empty($data[self::PAYFRAME_TOKEN])) {
            throw new LocalizedException(__('Your card is incorrect!'));
        }

        if (empty($data[self::TRANSACTION_AMOUNT])) {
            throw new LocalizedException(__('You must enter correct transaction data!'));
        }

        if (empty($data[self::TRANSACTION_CURRENCY])) {
            throw new LocalizedException(__('You must enter correct transaction data!'));
        }
    }
}
