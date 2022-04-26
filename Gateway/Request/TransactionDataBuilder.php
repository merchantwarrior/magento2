<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

/**
 * Transaction data builder
 */
class TransactionDataBuilder extends AbstractDataBuilder
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

        $order = $paymentDO->getOrder();

        return [
            RequestApiInterface::TRANSACTION_AMOUNT
                => $this->getTransactionAmount((float)$order->getGrandTotalAmount()),
            RequestApiInterface::TRANSACTION_CURRENCY
                => $order->getCurrencyCode(),
            RequestApiInterface::TRANSACTION_PRODUCT
                => 'ORDER_ID ' . $order->getOrderIncrementId()
        ];
    }
}
