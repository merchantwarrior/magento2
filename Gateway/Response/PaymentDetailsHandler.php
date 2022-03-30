<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper;
use Magento\Sales\Model\Order\Payment;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var MerchantWarriorLogger
     */
    private MerchantWarriorLogger $logger;

    /**
     * @param MerchantWarriorLogger $logger
     */
    public function __construct(
        MerchantWarriorLogger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        if (isset($response['responseCode']) && $response['responseCode'] === '0') {
            try {
                $payment->setAdditionalInformation('responseMessage', $response['responseMessage']);
                $payment->setAdditionalInformation('transactionID', $response['transactionID']);
                $payment->setAdditionalInformation('paymentCardNumber', $response['paymentCardNumber']);
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
}
