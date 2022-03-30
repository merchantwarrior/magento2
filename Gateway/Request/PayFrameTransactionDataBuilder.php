<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

/**
 * PayFrameTransactionDataBuilder data builder
 */
class PayFrameTransactionDataBuilder extends AbstractDataBuilder
{
    /**
     * Add delivery\billing details into request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();

        $result = [
            RequestApiInterface::PAYFRAME_TOKEN
                => $payment->getAdditionalInformation(RequestApiInterface::PAYFRAME_TOKEN),
            RequestApiInterface::PAYFRAME_KEY
                => $payment->getAdditionalInformation(RequestApiInterface::PAYFRAME_KEY)
        ];

        if ($tdsToken = $payment->getAdditionalInformation(RequestApiInterface::PAYFRAME_THREE_DS_TOKEN)) {
            $result[RequestApiInterface::PAYFRAME_THREE_DS_TOKEN] = $tdsToken;
        }
        return $result;
    }
}