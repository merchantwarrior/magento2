<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Model\MethodInterface;

class PaymentAction implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => MethodInterface::ACTION_AUTHORIZE,
                'label' => __('Authorize Only'),
            ],
            [
                'value' => MethodInterface::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture'),
            ]
        ];
    }
}
