<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use MerchantWarrior\Payment\Api\Direct\ProcessAuthInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessCaptureInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Api\Direct\RefundCardInterface;
use MerchantWarrior\Payment\Api\Payframe\ProcessInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

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
        $hash = '';
        switch ($data[RequestApiInterface::METHOD]) {
            case ProcessVoidInterface::API_METHOD:
                $hash = $this->prepareVoidTypeHash($data);
                break;
            case ProcessAuthInterface::API_METHOD:
            case RefundCardInterface::API_METHOD:
            case ProcessCaptureInterface::API_METHOD:
            case ProcessInterface::API_METHOD_CARD:
            case ProcessInterface::API_METHOD_AUTH:
                $hash = $this->prepareTransactionTypeHash($data);
                break;
        }
        return $hash;
    }

    /**
     * Prepare hash for query type
     * Algorithm: md5(apiPassphrase) + merchantUUID + transactionID OR transactionReferenceID
     *
     * @param array $data
     *
     * @return string
     */
    private function prepareQueryTypeHash(array $data): string
    {
        $apiPassPhrase = $this->config->getPassPhrase();
        $merchantUUID  = $this->config->getMerchantUserId();

        $transactionID = $data[RequestApiInterface::TRANSACTION_ID];

        $hash = md5($apiPassPhrase) . $merchantUUID . $transactionID;

        return md5(strtolower($hash));
    }

    /**
     * Prepare hash for transaction type
     * Params: md5(apiPassphrase) + merchantUUID + transactionAmount + transactionCurrency
     *
     * @param array $data
     *
     * @return string
     */
    private function prepareTransactionTypeHash(array $data): string
    {
        $apiPassPhrase = $this->config->getPassPhrase();
        $merchantUUID  = $this->config->getMerchantUserId();

        $transactionAmount   = $data[RequestApiInterface::TRANSACTION_AMOUNT];
        $transactionCurrency = $data[RequestApiInterface::TRANSACTION_CURRENCY];

        $hash = md5($apiPassPhrase) . $merchantUUID . $transactionAmount . $transactionCurrency;

        return md5(strtolower($hash));
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
        $merchantUUID  = $this->config->getMerchantUserId();

        $transactionID = $data[RequestApiInterface::TRANSACTION_ID];

        $hash = md5($apiPassPhrase) . strtolower($merchantUUID . $transactionID);

        return md5($hash);
    }
}
