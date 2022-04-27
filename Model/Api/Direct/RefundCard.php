<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Direct;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Direct\RefundCardInterface;
use MerchantWarrior\Payment\Model\Api\RequestApi;

class RefundCard extends RequestApi implements RefundCardInterface
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
        if (empty($data[self::TRANSACTION_AMOUNT])) {
            throw new LocalizedException(__('Transaction Amount is missed!'));
        }

        if (empty($data[self::TRANSACTION_CURRENCY])) {
            throw new LocalizedException(__('Transaction Currency is missed!'));
        }

        if (empty($data[self::TRANSACTION_ID])) {
            throw new LocalizedException(__('Transaction ID is missed!'));
        }

        if (empty($data[self::REFUND_AMOUNT])) {
            throw new LocalizedException(__('Refund Amount is missed!'));
        }
    }
}
