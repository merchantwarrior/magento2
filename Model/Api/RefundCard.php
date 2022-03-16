<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api;

use MerchantWarrior\Payment\Api\RefundCardInterface;
use Magento\Framework\Exception\LocalizedException;

class RefundCard extends AbstractApi implements RefundCardInterface
{
    /**
     * @inheritdoc
     */
    public function execute(
        float $transactionAmount,
        string $currency,
        string $transactionId,
        float $refundAmount
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
     */
    private function sendRequest(array $data): array
    {
        $data = $this->formData($data);

        $this->sendPostRequest(RefundCardInterface::API_METHOD, $data);

        if ($this->getStatus() === 200 && !$this->getResponse(RefundCardInterface::API_METHOD)->isEmpty()) {
            return $this->getResponse(RefundCardInterface::API_METHOD)->toArray();
        }

        if ($errorMessage = $this->getErrorMessage(RefundCardInterface::API_METHOD)) {
            throw new LocalizedException(__($errorMessage));
        }
        return [];
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

    }
}
