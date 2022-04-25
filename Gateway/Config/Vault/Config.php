<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Config\Vault;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as PaymentConfig;
use MerchantWarrior\Payment\Model\Config as MWConfig;

class Config extends PaymentConfig
{
    /**#@+
     * Configuration constants
     */
    public const CC_VAULT_CVV = 'cc_vault_cvv';
    /**#@-*/

    /**
     * @var MWConfig
     */
    private MWConfig $config;

    /**
     * Config constructor.
     *
     * @param MWConfig $config
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        MWConfig $config,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        string $pathPattern = PaymentConfig::DEFAULT_PATH_PATTERN
    ) {
        $this->config = $config;
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Check is CVV enabled
     *
     * @return bool
     */
    public function isCvvVerifyEnabled(): bool
    {
        return (bool)$this->getValue(self::CC_VAULT_CVV, $this->config->getStoreId());
    }
}
