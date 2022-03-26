<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

/**
 * Transaction data builder
 */
class TransactionDataBuilder extends AbstractDataBuilder implements BuilderInterface
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
            RequestApiInterface::TRANSACTION_AMOUNT   => $order->getGrandTotalAmount(),
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
}
