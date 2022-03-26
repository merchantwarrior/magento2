<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client\PayFrame;

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
     * @param TransferInterface $transferObject
     * @return array|string
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $transactionData = $transferObject->getBody();

        if (count($transactionData)) {
            $result = $this->processCard->execute($transactionData);
        }

        if (!empty($request['resultCode'])) {
            return $request;
        }

        return [];
    }
}
