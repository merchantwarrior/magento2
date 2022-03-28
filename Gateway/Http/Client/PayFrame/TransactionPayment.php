<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client\PayFrame;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Payframe\ProcessCardInterface;

class TransactionPayment implements ClientInterface
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

            /**
             * Temp data
             */
            $result = [
                "cardExpiryYear" => "21",
                "responseMessage" => "Transaction approved",
                "transactionReferenceID" => "12345",
                "cardType" => "mc",
                "responseCode" => "0",
                "authCode" => "731357421",
                "transactionAmount" => "1.00",
                "authResponseCode" => "08",
                "transactionID" => "1336-20be3569-b600-11e6-b9c3-005056b209e0",
                "receiptNo" => "731357421",
                "cardExpiryMonth" => "05",
                "customHash" => "65b172551b7d3a0706c0ce5330c98470",
                "authSettledDate" => "2016-11-29",
                "paymentCardNumber" => "512345XXXXXX2346",
                "authMessage" => "Honour with identification"
            ];
        }

        if ($result['responseCode'] === '0') {
            return $result;
        }
        return [];
    }
}
