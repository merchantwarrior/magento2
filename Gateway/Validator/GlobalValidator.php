<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use MerchantWarrior\Payment\Model\Service\IsCurrencyAllowed;

class GlobalValidator extends AbstractValidator
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
        if (!$this->isCurrencyAllowed->execute($validationSubject['payment'])) {
            return $this->createResult(
                false,
                [
                    __('Current currency is not allowed for Merchant Warrior payment method.')
                ]
            );
        }
        return $this->createResult(true);
    }
}
