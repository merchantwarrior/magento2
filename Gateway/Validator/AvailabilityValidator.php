<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use MerchantWarrior\Payment\Model\Service\IsCurrencyAllowed;

class AvailabilityValidator extends AbstractValidator
{
    /**
     * @var IsCurrencyAllowed
     */
    private $isCurrencyAllowed;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param IsCurrencyAllowed  $isCurrencyAllowed
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        IsCurrencyAllowed $isCurrencyAllowed
    ) {
        parent::__construct($resultFactory);
        $this->isCurrencyAllowed = $isCurrencyAllowed;
    }

    /**
     * Validate
     *
     * @param array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $paymentMethod = $validationSubject['payment'];
        $currency = $paymentMethod->getOrder()->getCurrencyCode();

        if (!$this->isCurrencyAllowed->execute($paymentMethod->getPayment()->getMethod(), $currency)) {
            return $this->createResult(
                false,
                [
                    __('Currency: %1 not allowed for Merchant Warrior payment method.', $currency)
                ]
            );
        }
        return $this->createResult(true);
    }
}
