<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

/**
 * Class CustomerDataBuilder
 */
class CustomerIpDataBuilder extends AbstractDataBuilder implements BuilderInterface
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

        $order = $paymentDO->getOrder();

        return [
            RequestApiInterface::CUSTOMER_IP => $order->getRemoteIp()
        ];
    }
}
