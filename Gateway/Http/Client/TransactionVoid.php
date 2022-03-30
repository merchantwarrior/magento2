<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;

class TransactionVoid implements ClientInterface
{
    /**
     * @var ProcessVoidInterface
     */
    private ProcessVoidInterface $process;

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

        if (count($transactionData)) {
            try {
                //$result = $this->process->execute(ProcessInterface::API_METHOD_AUTH, $transactionData);
            } catch (LocalizedException $err) {
                $result = [
                    'responseCode' => $err->getCode(),
                    'error' => $err->getMessage()
                ];
            }
            return $result;
        }
        return [];
    }
}
