<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class AddressDataBuilder implements BuilderInterface
{
    /**
     * Add delivery\billing details into request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [];
    }
}
