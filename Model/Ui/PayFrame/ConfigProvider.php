<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Ui\PayFrame;

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
    public const METHOD_CODE = 'merchant_warrior_payframe';
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
        if (!($paymentMethod = $this->getPaymentMethod()) || !$this->config->isPayFrameActive()) {
            return [];
        }

        return $paymentMethod->isAvailable() ? [
            'payment' => [
                self::METHOD_CODE => [
                    'active'    => $this->config->isPayFrameActive(),
                    'uuid'      => $this->config->getMerchantUserId(),
                    'apiKey'    => $this->config->getApiKey(),
                    'payframeSrc' => $this->getPayFrameSrc(),
                    'submitURL'   => $this->getSubmitUrl(),
                    'allowedTypeCards' => $this->getAllowedCCList(),
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
     * Get allowed CC List
     *
     * @return string|null
     */
    private function getAllowedCCList(): ?string
    {
        $allowedCreditCards = $this->config->getPayFrameAllowedTypeCards();
        $cardsTypes = $this->config->getCcTypes();

        $creditCards = [];
        foreach ($allowedCreditCards as $card) {
            if (isset($cardsTypes[$card])) {
                $creditCards[] = $cardsTypes[$card]['code_alt'];
            }
        }
        return implode(',', $creditCards);
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
