<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

class CustomerDataBuilder extends AbstractDataBuilder
{
    /**
     * Add shopper data into request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        return [
            RequestApiInterface::CUSTOMER_NAME  => $customerName,
            RequestApiInterface::CUSTOMER_PHONE => $billingAddress->getTelephone(),
            RequestApiInterface::CUSTOMER_EMAIL => $billingAddress->getEmail()
        ];
    }
}
