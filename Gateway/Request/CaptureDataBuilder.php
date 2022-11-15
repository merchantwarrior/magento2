<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

/**
 * Class CaptureDataBuilder
 */
class CaptureDataBuilder extends AbstractDataBuilder
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        return [
            'transactionAmount' => $this->getTransactionAmount((float)$payment->getBaseAmountOrdered()),
            'transactionCurrency' => $order->getCurrencyCode(),
            'transactionID' => $this->clearTransactionId($payment->getTransactionId()),
            'captureAmount' => $this->getTransactionAmount((float)$payment->getBaseAmountOrdered())
        ];
    }
}
