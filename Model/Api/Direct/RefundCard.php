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
    public function execute(
        string $transactionAmount,
        string $currency,
        string $transactionId,
        string $refundAmount
    ): array {
        if (!$this->config->isEnabled()) {
            return [];
        }

        return $this->sendRequest([
            self::METHOD => self::API_METHOD,
            self::TRANSACTION_AMOUNT => $transactionAmount,
            self::TRANSACTION_CURRENCY => $currency,
            self::TRANSACTION_ID => $transactionId,
            self::REFUND_AMOUNT => $refundAmount
        ]);
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

        $this->sendPostRequest(self::API_METHOD, 'post/', $data);

        if ($this->getResponseCode(self::API_METHOD) !== '0') {
            throw new LocalizedException(__($this->getResponseMessage(self::API_METHOD)));
        }
        return $this->getResponse(self::API_METHOD)->toArray();
    }

    /**
     * Validate
     *
     * @param string $date
     *
     * @return void
     * @throws LocalizedException
     */
    private function validate(string $date): void
    {
        // TODO: Add additonal validation for data
    }
}
