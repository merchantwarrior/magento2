<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

/**
 * DirectApiTransactionDataBuilder data builder
 */
class DirectApiTransactionDataBuilder extends AbstractDataBuilder
{
    /**
     * Add card details into request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();

        // Formatting expiry field value according to api
        $leadingZero = strlen($payment->getCcExpMonth()) == 1 ? "0" : "";
        $expiry = $leadingZero . $payment->getCcExpMonth() . substr($payment->getCcExpYear(), -2);

        $result = [
            RequestApiInterface::PAYMENT_CARD_NUMBER    => $payment->getCcNumber(),
            RequestApiInterface::PAYMENT_CARD_CSC       => $payment->getCcCid(),
            RequestApiInterface::PAYMENT_CARD_NAME      => $payment->getCcOwner(),
            RequestApiInterface::PAYMENT_CARD_EXPIRY    => $expiry,
            RequestApiInterface::PAYMENT_CARD_TYPE      => $payment->getCcType()
        ];

        if ($tdsToken = $payment->getAdditionalInformation(RequestApiInterface::PAYFRAME_TDS_TOKEN)) {
            $result[RequestApiInterface::PAYFRAME_THREE_DS_TOKEN] = $tdsToken;
        }
        return $result;
    }
}
