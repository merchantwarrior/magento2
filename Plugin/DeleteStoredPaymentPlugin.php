<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use MerchantWarrior\Payment\Api\Token\RemoveCardInterface;

class DeleteStoredPaymentPlugin
{
    /**
     * @var RemoveCardInterface
     */
    private RemoveCardInterface $removeCard;

    /**
     * @var string
     */
    private string $token;

    /**
     * Remove saved cart constructor
     *
     * @param RemoveCardInterface $removeCard
     */
    public function __construct(
        RemoveCardInterface $removeCard
    ) {
        $this->removeCard = $removeCard;
    }

    /**
     * Before delete action
     *
     * @param PaymentTokenRepositoryInterface $subject
     * @param PaymentTokenInterface $paymentToken
     *
     * @return null
     */
    public function beforeDelete(
        PaymentTokenRepositoryInterface $subject,
        PaymentTokenInterface $paymentToken
    ) {
        $this->token = $paymentToken->getGatewayToken();

        return null;
    }

    /**
     * After delete action
     *
     * @param PaymentTokenRepositoryInterface $subject
     * @param bool $result
     *
     * @return bool
     * @throws LocalizedException
     */
    public function afterDelete(PaymentTokenRepositoryInterface $subject, bool $result): bool
    {
        if ($result && $this->token) {
            $this->removeCard->execute($this->token);
        }
        return $result;
    }
}
