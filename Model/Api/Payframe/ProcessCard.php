<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api\Payframe;

use Magento\Framework\Exception\LocalizedException;
use MerchantWarrior\Payment\Api\Payframe\ProcessCardInterface;
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

        return $this->sendRequest($transactionParams);
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

        $this->sendPostRequest(self::API_METHOD, 'payframe/', $data);

        if ($this->getResponseCode(self::API_METHOD) !== '0') {
            throw new LocalizedException(__($this->getResponseMessage(self::API_METHOD)));
        }
        return $this->getResponse(self::API_METHOD)->toArray();
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