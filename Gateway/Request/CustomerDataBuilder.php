<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class CustomerDataBuilder implements BuilderInterface
{
    /**
     * Add shopper data into request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject): array
    {
        return [];
    }
}
