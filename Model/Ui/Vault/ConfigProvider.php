<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Ui\Vault;

use MerchantWarrior\Payment\Gateway\Config\Vault\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use MerchantWarrior\Payment\Model\Service\GetPaymentIconsList;

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
     * @var GetPaymentIconsList
     */
    private GetPaymentIconsList $getPaymentIconsList;

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     * @param GetPaymentIconsList $getPaymentIconsList
     */
    public function __construct(
        Config $config,
        GetPaymentIconsList $getPaymentIconsList
    ) {
        $this->config = $config;
        $this->getPaymentIconsList = $getPaymentIconsList;
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
                    'cvvVerify' => $this->config->isCvvVerifyEnabled(),
                    'icons' => $this->getPaymentIconsList->execute()
                ]
            ]
        ];
    }
}
