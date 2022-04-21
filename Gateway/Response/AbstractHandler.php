<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Response;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;
use MerchantWarrior\Payment\Model\TransactionManagement;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var MerchantWarriorLogger
     */
    protected MerchantWarriorLogger $logger;

    /**
     * @var TransactionManagement
     */
    private TransactionManagement $transactionManagement;

    /**
     * @param Session $checkoutSession
     * @param TransactionManagement $transactionManagement
     * @param MerchantWarriorLogger $logger
     */
    public function __construct(
        Session $checkoutSession,
        TransactionManagement $transactionManagement,
        MerchantWarriorLogger $logger
    ) {
        $this->logger = $logger;
        $this->transactionManagement = $transactionManagement;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Fill additional data
     *
     * @param Payment $payment
     * @param array $response
     *
     * @return void
     * @throws AlreadyExistsException
     */
    protected function fillAdditionalData(Payment $payment, array $response): void
    {
        try {
            $payment->setAdditionalInformation('responseMessage', $response['responseMessage']);
            $payment->setAdditionalInformation('transactionID', $response['transactionID']);
            if (isset($response['paymentCardNumber'])) {
                $payment->setAdditionalInformation('paymentCardNumber', $response['paymentCardNumber']);
            }
            if (isset($response['cardType'])) {
                $payment->setAdditionalInformation('cardType', $response['cardType']);
            }
            if (isset($response['cardExpiryMonth'])) {
                $payment->setAdditionalInformation('cardExpiryMonth', $response['cardExpiryMonth']);
            }
            if (isset($response['cardExpiryYear'])) {
                $payment->setAdditionalInformation('cardExpiryYear', $response['cardExpiryYear']);
            }
            if (isset($response['authSettledDate'])) {
                $payment->setAdditionalInformation('authSettledDate', $response['authSettledDate']);
            }
        } catch (LocalizedException $exp) {
            $this->logger->error($exp->getMessage());
        }
        $payment->setTransactionId($response['transactionID']);

        $this->transactionManagement->create($payment->getOrder()->getIncrementId(), $response['transactionID']);
    }

    /**
     * Clear additional data
     *
     * @param Payment $payment
     * @param array $response
     *
     * @return void
     */
    protected function clearAdditionalData(Payment $payment, array $response): void
    {
        $payment->setTransactionId('');
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
