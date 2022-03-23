<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;

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
                self::CODE => [
                    'enabled'   => $this->config->isEnabled(),
                    'uuid'      => $this->config->getMerchantUserId(),
                    'apiKey'    => $this->config->getApiKey(),
                    'payframeSrc' => $this->getPayFrameSrc(),
                    'submitURL'   => $this->getSubmitUrl(),
                    'allowedTypeCards' => '',
                    'successPage' => $this->urlBuilder->getUrl(
                        'checkout/onepage/success',
                        [
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    )
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
     * Get pay frame src URL
     *
     * @return string
     */
    private function getPayFrameSrc(): string
    {
        return $this->config->isSandBoxModeEnabled()
            ? 'https://securetest.merchantwarrior.com/payframe/' : 'https://secure.merchantwarrior.com/payframe/';
    }

    /**
     * Get submit URL
     *
     * @return string
     */
    private function getSubmitUrl(): string
    {
        return $this->config->getApiUrl() . 'payframe/';
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
