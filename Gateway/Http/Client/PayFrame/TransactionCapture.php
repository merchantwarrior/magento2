<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client\PayFrame;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Payframe\ProcessCardInterface;

class TransactionCapture implements ClientInterface
{
    /**
     * @var ProcessCardInterface
     */
    private ProcessCardInterface $processCard;

    /**
     * @param ProcessCardInterface $processCard
     */
    public function __construct(
        ProcessCardInterface $processCard
    ) {
        $this->processCard = $processCard;
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
                $result = $this->processCard->execute($transactionData);
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
