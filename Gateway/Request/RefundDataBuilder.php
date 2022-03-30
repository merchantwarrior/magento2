<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

/**
 * Class RefundDataBuilder
 */
class RefundDataBuilder extends AbstractDataBuilder
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
        $creditMemo = $payment->getCreditMemo();

        return [
            'transactionAmount' => $this->getTransactionAmount($creditMemo->getGrandTotal()),
            'transactionCurrency' => $creditMemo->getOrderCurrencyCode(),
            'transactionID' => $this->clearTransactionId($payment->getTransactionId()),
            'refundAmount' => $this->getTransactionAmount($creditMemo->getGrandTotal())
        ];
    }
}
