<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Store\Model\ScopeInterface;
use MerchantWarrior\Payment\Model\Config;

/**
 * Class CountryValidator
 */
class CountryValidator extends AbstractValidator
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    protected $allowedSpecificConfigPath = Config::XML_PATH_PAYFRAME_ALLOWED_SPECIFIC;

    /**
     * @var string
     */
    protected $allowedCountryConfigPath = Config::XML_PATH_PAYFRAME_ALLOWED_SPECIFICCOUNTRY;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($resultFactory);
    }

    /**
     * Validate countries
     *
     * @param array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;
        $storeId = $validationSubject['storeId'];

        $allowedSpecific = $this->scopeConfig->getValue(
            $this->allowedSpecificConfigPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ((int)$allowedSpecific === 1) {
            $availableCountries = $this->scopeConfig->getValue(
                $this->allowedCountryConfigPath,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $availableCountries = explode(',', $availableCountries);

            if (!in_array($validationSubject['country'], $availableCountries, true)) {
                $isValid = false;
            }
        }

        return $this->createResult($isValid);
    }
}
