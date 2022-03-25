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
     * @param array $validationSubject
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDataObjectInterface = SubjectReader::readPayment($validationSubject);
        $payment = $paymentDataObjectInterface->getPayment();

        $payment->setAdditionalInformation('3dActive', false);
        $isValid = true;
        $errorMessages = [];

        // validate result
        if (!empty($response['resultCode'])) {
            $resultCode = $response['resultCode'];
            $payment->setAdditionalInformation('resultCode', $resultCode);

            if (!empty($response['action'])) {
                $payment->setAdditionalInformation('action', $response['action']);
            } else {
                // No further action needed, so payment result is conclusive
                $this->checkoutSession->unsPendingPayment();
            }

            switch ($resultCode) {
                case "Authorised":
                case "Received":
                    // Save cc_type if available in the response
                    if (!empty($response['additionalData']['paymentMethod'])) {
//                        $payment->setAdditionalInformation('cc_type', $ccType);
//                        $payment->setCcType($ccType);
                    }
                    break;
                case "IdentifyShopper":
                case "ChallengeShopper":
                case "PresentToShopper":
                case 'Pending':
                case "RedirectShopper":
                    // nothing extra
                    break;
                case "Refused":
                    $errorMsg = __('The payment is REFUSED.');
                    // this will result the specific error
                    throw new LocalizedException($errorMsg);
                default:
                    $errorMsg = __('Error with payment method please select different payment method.');
                    throw new LocalizedException($errorMsg);
            }
        } else {
            if (!empty($response['error'])) {
                $this->logger->error($response['error']);
            }

            $errorMsg = __('Error with payment method please select different payment method.');
            throw new LocalizedException($errorMsg);
        }

        return $this->createResult($isValid, $errorMessages);
    }
}
