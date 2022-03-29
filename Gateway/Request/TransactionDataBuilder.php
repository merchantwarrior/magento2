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
            RequestApiInterface::TRANSACTION_AMOUNT   => $this->getTransactionAmount($order),
            RequestApiInterface::TRANSACTION_CURRENCY => $order->getCurrencyCode(),
            RequestApiInterface::TRANSACTION_PRODUCT  => $this->getProductData($order)
        ];
    }

    /**
     * Get product data
     *
     * @param OrderAdapterInterface $order
     *
     * @return string
     */
    private function getProductData(OrderAdapterInterface $order): string
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = $item->getSku();
        }
        return implode(',', $items);
    }

    /**
     * Get formatted amount
     *
     * @param OrderAdapterInterface $order
     *
     * @return string
     */
    private function getTransactionAmount(OrderAdapterInterface $order): string
    {
        $price = (float)$order->getGrandTotalAmount();

        return number_format($price, 2, '.', '');
    }
}
