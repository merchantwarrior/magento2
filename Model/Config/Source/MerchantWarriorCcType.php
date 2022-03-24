<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Config\Source;

use Magento\Payment\Model\Source\Cctype;

/**
 * List of allowed Credit Cards
 */
class MerchantWarriorCcType extends Cctype
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'VISA', 'label' => 'Visa'],
            ['value' => 'MASTERCARD', 'label' => 'MasterCard'],
            ['value' => 'AMEX', 'label' => 'AMEX'],
            ['value' => 'DINERS', 'label' => 'Diners'],
            ['value' => 'DISCOVER', 'label' => 'Discover'],
            ['value' => 'JCB', 'label' => 'JCB'],
            ['value' => 'UNIONPAY', 'label' => 'UnionPay']
        ];
    }
}
