<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class CheckoutResponseValidator extends AbstractValidator
{
    /**
     * @var MerchantWarriorLogger
     */
    private MerchantWarriorLogger $logger;

    /**
     * CheckoutResponseValidator constructor.
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param MerchantWarriorLogger $logger
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        MerchantWarriorLogger $logger
    ) {
        $this->logger = $logger;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject = [ 'payment' => '', 'amount' => '', 'response' => '' ]
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDataObjectInterface = SubjectReader::readPayment($validationSubject);
        $payment = $paymentDataObjectInterface->getPayment();

        $errorMessages = [];

        if (isset($response['responseCode']) && $response['responseCode'] === '0') {
            $payment->setAdditionalInformation('responseMessage', $response['responseMessage']);
            $payment->setAdditionalInformation('transactionID', $response['transactionID']);
            $payment->setAdditionalInformation('paymentCardNumber', $response['paymentCardNumber']);
        } else {
            if (!empty($response['error'])) {
                $this->logger->error($response['error']);
            }
            return $this->createResult(false, [$response['error']]);
        }
        return $this->createResult(true, $errorMessages);
    }
}
