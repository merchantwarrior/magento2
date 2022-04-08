<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Response\Vault;

use Magento\Sales\Model\Order\Payment;
use MerchantWarrior\Payment\Gateway\Response\AbstractHandler;

class SaleDetailsHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        if (isset($response['responseCode']) && $response['responseCode'] === '0') {
            $this->fillAdditionalData($payment, $response);
        }
    }
}
