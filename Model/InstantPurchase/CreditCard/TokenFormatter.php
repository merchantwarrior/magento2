<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\InstantPurchase\CreditCard;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use MerchantWarrior\Payment\Model\Config;

/**
 * Merchant Warrior vaulted credit cards formatter
 *
 * Class TokenFormatter
 */
class TokenFormatter implements PaymentTokenFormatterInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param Config $config
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Config $config,
        SerializerInterface $serializer
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string
    {
        $details = $this->serializer->unserialize($paymentToken->getTokenDetails() ?: '{}');
        if (!isset($details['type'], $details['maskedCC'], $details['expirationDate'])) {
            throw new \InvalidArgumentException('Invalid credit card token details.');
        }

        $cardsTypes = $this->config->getCcTypes();

        $ccType = $details['type'];
        foreach ($cardsTypes as $key => $card) {
            if ($key === $details['type']) {
                $ccType = $card['label'];
            }
        }

        return sprintf(
            '%s: %s, %s: %s (%s: %s)',
            __('Credit Card'),
            $ccType,
            __('ending'),
            $details['maskedCC'],
            __('expires'),
            $details['expirationDate']
        );
    }
}
