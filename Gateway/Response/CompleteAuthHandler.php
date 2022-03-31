<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Response;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class CompleteAuthHandler implements HandlerInterface
{
    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var MerchantWarriorLogger
     */
    private MerchantWarriorLogger $logger;

    /**
     * @param Session $checkoutSession
     * @param MerchantWarriorLogger $logger
     */
    public function __construct(
        Session $checkoutSession,
        MerchantWarriorLogger $logger
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $this->getOrder();

        if (isset($response['responseCode']) && $response['responseCode'] === '0') {
            try {
                $payment->setAdditionalInformation('responseMessage', $response['responseMessage']);
                $payment->setAdditionalInformation('transactionID', $response['transactionID']);
                $payment->setAdditionalInformation('paymentCardNumber', $response['paymentCardNumber']);

                $payment->setIsTransactionPending(true);
                $order->setState(Order::STATE_PENDING_PAYMENT)
                    ->setStatus(Order::STATE_PENDING_PAYMENT);
                $payment->setIsTransactionClosed(false);
            } catch (LocalizedException $exp) {
                $this->logger->error($exp->getMessage());
            }
            $payment->setTransactionId($response['transactionID']);
        }
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     *
     * @return PaymentDataObjectInterface
     */
    protected function readPayment(array $subject): PaymentDataObjectInterface
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * @return Order
     */
    protected function getOrder(): OrderInterface
    {
        return $this->checkoutSession->getLastRealOrder();
    }
}
