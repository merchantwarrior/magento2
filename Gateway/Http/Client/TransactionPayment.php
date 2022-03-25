<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransactionPayment implements ClientInterface
{
    /**
     * @param TransferInterface $transferObject
     * @return array|string
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        if (!empty($request['resultCode'])) {
            return $request;
        }

        return [];
    }
}
