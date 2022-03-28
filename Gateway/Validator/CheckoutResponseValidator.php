<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
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
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * CheckoutResponseValidator constructor.
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param MerchantWarriorLogger $logger
     * @param Session $checkoutSession
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        MerchantWarriorLogger $logger,
        Session $checkoutSession
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject = [ 'payment' => '', 'amount' => '', 'response' => '' ]
     *
     * @return ResultInterface
     * @throws LocalizedException
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

            $errorMsg = __('Error with payment method please select different payment method.');
            throw new LocalizedException($errorMsg);
        }

        return $this->createResult(true, $errorMessages);
    }
}
