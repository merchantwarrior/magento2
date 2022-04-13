<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessAuthInterface;

class TransactionAuthorize implements ClientInterface
{
    /**
     * @var ProcessAuthInterface
     */
    private ProcessAuthInterface $process;

    /**
     * @param ProcessAuthInterface $process
     */
    public function __construct(
        ProcessAuthInterface $process
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
                $result = $this->process->execute($transactionData);
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
