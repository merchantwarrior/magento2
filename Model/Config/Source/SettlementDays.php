<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SettlementDays implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 2,
                'label' => 2,
            ],
            [
                'value' => 3,
                'label' => 3,
            ],
            [
                'value' => 4,
                'label' => 4,
            ],
            [
                'value' => 5,
                'label' => 5,
            ],
            [
                'value' => 6,
                'label' => 6,
            ],
            [
                'value' => 7,
                'label' => 7,
            ]
        ];
    }
}
