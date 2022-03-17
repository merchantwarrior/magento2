<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api;

use MerchantWarrior\Payment\Api\ProcessVoidInterface;
use Magento\Framework\Exception\LocalizedException;

class ProcessVoid extends RequestApi implements ProcessVoidInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $transactionId): array
    {
        if (!$this->config->isEnabled()) {
            return [];
        }

        return $this->sendRequest([
            self::METHOD => self::API_METHOD,
            self::TRANSACTION_ID => $transactionId
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

        $this->sendPostRequest(self::API_METHOD, $data);

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
        // TODO: Add additional validation
    }
}
