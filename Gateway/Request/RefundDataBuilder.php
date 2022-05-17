<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

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
            RequestApiInterface::TRANSACTION_ID => $this->clearTransactionId(
                $payment->getAdditionalInformation(RequestApiInterface::TRANSACTION_ID)
            ),
            RequestApiInterface::TRANSACTION_AMOUNT => $this->getTransactionAmount($creditMemo->getBaseGrandTotal()),
            RequestApiInterface::TRANSACTION_CURRENCY => $creditMemo->getBaseCurrencyCode(),
            RequestApiInterface::REFUND_AMOUNT => $this->getTransactionAmount($creditMemo->getBaseGrandTotal())
        ];
    }
}
