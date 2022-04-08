<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Ui\Vault;

use MerchantWarrior\Payment\Gateway\Config\Vault\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**#@+
     * Method code constant
     */
    public const METHOD_CODE = 'merchant_warrior_cc_vault';
    /**#@-*/

    /**
     * @var Config
     */
    private Config $config;

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::METHOD_CODE => [
                    'cvvVerify' => $this->config->isCvvVerifyEnabled()
                ]
            ]
        ];
    }
}
