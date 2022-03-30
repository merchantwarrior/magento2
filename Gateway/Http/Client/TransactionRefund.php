<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Direct\RefundCardInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

class TransactionRefund implements ClientInterface
{
    /**
     * @var RefundCardInterface
     */
    private RefundCardInterface $process;

    /**
     * @param RefundCardInterface $process
     */
    public function __construct(
        RefundCardInterface $process
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
                $result = $this->process->execute(
                    $transactionData[RequestApiInterface::TRANSACTION_AMOUNT],
                    $transactionData[RequestApiInterface::TRANSACTION_CURRENCY],
                    $transactionData[RequestApiInterface::TRANSACTION_ID],
                    $transactionData[RequestApiInterface::REFUND_AMOUNT]
                );
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
