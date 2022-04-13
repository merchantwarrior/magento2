<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessCardInterface;

class TransactionSale implements ClientInterface
{
    /**
     * @var ProcessCardInterface
     */
    private ProcessCardInterface $process;

    /**
     * @param ProcessCardInterface $process
     */
    public function __construct(
        ProcessCardInterface $process
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
