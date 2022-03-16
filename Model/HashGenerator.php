<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use MerchantWarrior\Payment\Api\ProcessVoidInterface;
use MerchantWarrior\Payment\Api\RefundCardInterface;
use MerchantWarrior\Payment\Model\Api\AbstractApi;

class HashGenerator
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Generate hash string
     *
     * @param array $data
     *
     * @return string
     */
    public function execute(array $data): string
    {
        switch ($data[AbstractApi::METHOD]) {
            case RefundCardInterface::API_METHOD:
                $hash = $this->prepareQueryTypeHash($data);
                break;
            case ProcessVoidInterface::API_METHOD:
                $hash = $this->prepareVoidTypeHash($data);
                break;
        }
        return $hash;
    }

    /**
     * Prepare hash for query type
     *
     * @param array $data
     *
     * @return string
     */
    private function prepareQueryTypeHash(array $data): string
    {
        $apiPassPhrase = $this->config->getPassPhrase();

        $merchantUUID = $this->config->getMerchantUserId();

        $transactionID = $data[AbstractApi::TRANSACTION_ID];

        $hash = md5($apiPassPhrase) . strtolower($merchantUUID . $transactionID);

        return md5($hash);
    }

    /**
     * Prepare hash for void type
     *
     * @param array $data
     *
     * @return string
     */
    private function prepareVoidTypeHash(array $data): string
    {
        $apiPassPhrase = $this->config->getPassPhrase();

        $merchantUUID = $this->config->getMerchantUserId();

        $transactionID = $data[AbstractApi::TRANSACTION_ID];

        $hash = md5($apiPassPhrase) . strtolower($merchantUUID . $transactionID);

        return md5($hash);
    }
}
