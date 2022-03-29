<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider as MWConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as MWPayFrameConfigProvider;

class AvailabilityValidator extends AbstractValidator
{
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

        if (in_array(
                $paymentMethod->getPayment()->getMethod(),
                [
                    MWConfigProvider::METHOD_CODE,
                    MWPayFrameConfigProvider::METHOD_CODE
                ],
                true
            ) && !in_array($paymentMethod->getOrder()->getCurrencyCode(), ['AUD', 'NZD'])
        ) {
            return $this->createResult(
                false,
                [
                    __('Currency: %s not allowed for Merchant Warrior payment method.')
                ]
            );
        }
        return $this->createResult(true);
    }
}
