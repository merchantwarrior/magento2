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
     * @inheritDoc
     */
    public function getAllowedTypes(): array
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'MI', 'DN'];
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->_paymentConfig->getCcTypes() as $code => $name) {
            if (in_array($code, $allowed, true)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }
}
