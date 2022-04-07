<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Response\Vault;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use MerchantWarrior\Payment\Gateway\Response\AbstractHandler;

class CompleteAuthHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order   = $this->getOrder();

        if (isset($response['responseCode']) && $response['responseCode'] === '0') {
            $this->fillAdditionalData($payment, $response);

            $payment->setIsTransactionPending(true);
            $order->setState(Order::STATE_PENDING_PAYMENT)
                ->setStatus(Order::STATE_PENDING_PAYMENT);
            $payment->setIsTransactionClosed(false);
        }
    }
}
