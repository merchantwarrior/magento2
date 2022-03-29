<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client\PayFrame;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Payframe\ProcessAuthInterface;

class TransactionAuthorize implements ClientInterface
{
    /**
     * @var ProcessAuthInterface
     */
    private ProcessAuthInterface $processCard;

    /**
     * @param ProcessAuthInterface $processAuth
     */
    public function __construct(
        ProcessAuthInterface $processAuth
    ) {
        $this->processAuth = $processAuth;
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
                $result = $this->processAuth->execute($transactionData);
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
