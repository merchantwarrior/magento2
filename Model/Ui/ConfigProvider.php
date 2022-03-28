<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Ui;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
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
    /**#@-*/

    /**
     * @var string[]
     */
    protected $methodCode = self::METHOD_CODE;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var PaymentHelper
     */
    protected PaymentHelper $paymentHelper;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Config $config
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Config $config,
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->paymentHelper = $paymentHelper;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
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
