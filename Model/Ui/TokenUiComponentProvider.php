<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Ui;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Framework\UrlInterface;
use MerchantWarrior\Payment\Model\Config;

class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SerializerInterface
     */
    private $config;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param SerializerInterface $serializer
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        SerializerInterface $serializer,
        UrlInterface $urlBuilder,
        Config $config
    ) {
        $this->componentFactory = $componentFactory;
        $this->serializer = $serializer;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    /**
     * Get UI component for token
     *
     * @param PaymentTokenInterface $paymentToken
     *
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken): TokenUiComponentInterface
    {
        $jsonDetails = $this->serializer->unserialize($paymentToken->getTokenDetails() ?: '{}');
        $jsonDetails['cardID'] = $paymentToken->getGatewayToken();
        return $this->componentFactory->create(
            [
                'config' => [
                    'code' => ConfigProvider::CC_VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'MerchantWarrior_Payment/js/view/payment/method-renderer/'.($this->config->isSandBoxModeEnabled()? "vaultSandbox" : "vault")
            ]
        );
    }

    /**
     * Get url to retrieve payment method nonce
     *
     * @return string
     */
    private function getNonceRetrieveUrl(): string
    {
        return $this->urlBuilder->getUrl(
            ConfigProvider::METHOD_CODE . '/payment/getnonce',
            [
                '_secure' => true
            ]
        );
    }
}
