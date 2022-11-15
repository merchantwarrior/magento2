<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

class TransactionVoid implements ClientInterface
{
    /**
     * @var ProcessVoidInterface
     */
    private $process;

    /**
     * @param ProcessVoidInterface $process
     */
    public function __construct(
        ProcessVoidInterface $process
    ) {
        $this->process = $process;
    }

    /**
     * Place request
     *
     * @param TransferInterface $transferObject
     *
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $transactionData = $transferObject->getBody();

        if (count($transactionData) && isset($transactionData[RequestApiInterface::TRANSACTION_ID])) {
            try {
                $result = $this->process->execute($transactionData[RequestApiInterface::TRANSACTION_ID]);
            } catch (LocalizedException $err) {
                $result = $this->process->getError(ProcessVoidInterface::API_METHOD);
                $result = $this->validateError($result);
            }
            return $result;
        }
        return [];
    }

    /**
     * Validate returned error
     * In case transaction been voided from MW sided - cancel order
     *
     * @param array $errorResult
     *
     * @return array|string[]
     */
    private function validateError(array $errorResult): array
    {
        if (0 !== strpos($errorResult['responseMessage'], 'transaction has already been voided')) {
            $errorResult = [
                'responseCode' => '0',
                'error' => ''
            ];
        }
        return $errorResult;
    }
}
