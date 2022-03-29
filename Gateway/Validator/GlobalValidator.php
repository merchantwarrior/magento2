<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider as MWConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as MWPayFrameConfigProvider;

class GlobalValidator extends AbstractValidator
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param StoreManagerInterface  $storeManager
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($resultFactory);
        $this->storeManager = $storeManager;
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

        if (in_array(
                $paymentMethod->getMethod(),
                [
                    MWConfigProvider::METHOD_CODE,
                    MWPayFrameConfigProvider::METHOD_CODE
                ],
                true
            ) && !in_array($this->getCurrency(), ['AUD', 'NZD'])
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

    /**
     * Get currency Code
     *
     * @return string|null
     */
    private function getCurrency(): ?string
    {
        try {
            return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        } catch (\Exception $e) {
            return null;
        }
    }
}
