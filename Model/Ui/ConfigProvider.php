<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Ui;

use Magento\Framework\App\RequestInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use MerchantWarrior\Payment\Model\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**#@+
     * Method code constant
     */
    public const METHOD_CODE = 'merchant_warrior';
    public const CC_VAULT_CODE = 'merchant_warrior_cc_vault';
    /**#@-*/

    /**
     * @var string[]
     */
    protected $methodCode = self::METHOD_CODE;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Config $config
     * @param RequestInterface $request
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Config $config,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->paymentHelper = $paymentHelper;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        if (!$paymentMethod = $this->getPaymentMethod()) {
            return [];
        }

        return $paymentMethod->isAvailable() ? [
            'payment' => [
                self::METHOD_CODE => [
                    'enabled'   => $this->config->isEnabled(),
                    'uuid'      => $this->config->getMerchantUserId(),
                    'apiKey'    => $this->config->getApiKey()
                ]
            ],
        ] : [];
    }

    /**
     * Get payment method
     *
     * @return MethodInterface|null
     */
    private function getPaymentMethod(): ?MethodInterface
    {
        try {
            return $this->paymentHelper->getMethodInstance($this->methodCode);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retrieve request object
     *
     * @return RequestInterface
     */
    protected function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
