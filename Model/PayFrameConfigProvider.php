<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as PaymentHelper;

class PayFrameConfigProvider implements ConfigProviderInterface
{
    const CODE = PaymentMethod::METHOD_CODE;
    const CC_VAULT_CODE = PaymentMethod::METHOD_CODE . '_vault';

    /**
     * @var string[]
     */
    protected $methodCode = PaymentMethod::METHOD_CODE;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var PaymentMethod
     */
    protected $method;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Config $config
     * @throws LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Config $config
    ) {
        $this->config = $config;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                self::CODE => [
                    'enabled'   => $this->config->isEnabled(),
                    'uuid'      => $this->config->getMerchantUserId(),
                    'apiKey'    => $this->config->getApiKey(),
                    'src'       => 'https://secure.merchantwarrior.com/payframe/', // camp | prod | production
                    'submitURL' => $this->config->getApiUrl() . 'payframe/'
                ]
            ],
        ] : [];
    }
}
