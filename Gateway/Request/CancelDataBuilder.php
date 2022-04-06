<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

/**
 * Class CancelDataBuilder
 */
class CancelDataBuilder extends AbstractDataBuilder
{
    /**
     * Create cancel request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        return [
            RequestApiInterface::TRANSACTION_ID
                => $payment->getAdditionalInformation(RequestApiInterface::TRANSACTION_ID)
        ];
    }
}
