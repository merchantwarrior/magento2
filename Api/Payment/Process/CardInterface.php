<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Payment\Process;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface CardInterface
 */
interface CardInterface
{
    /**
     * Process Card
     *
     * @param string $payframeToken
     * @param string $payframeKey
     * @param string $cartId
     * @param string $email
     * @param AddressInterface|null $billingAddress
     *
     * @return string
     */
    public function execute(
        string $payframeToken,
        string $payframeKey,
        string $cartId,
        string $email,
        AddressInterface $billingAddress = null
    ): string;
}
