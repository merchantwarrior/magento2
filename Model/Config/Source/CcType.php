<?php

namespace MerchantWarrior\Payment\Model\Config\Source;

use Magento\Payment\Model\Source\Cctype as CcTypeSource;

/**
 * @codeCoverageIgnore
 */
class CcType extends CcTypeSource
{
    // TODO
    // Stop using this const. Get the CC data from storage instead
    public const CARD_TYPE_MAP = [
        'AE' => 'American Express',
        'DI' => 'Discover',
        'JCB' => 'JCB',
        'MC' => 'MasterCard',
        'VI' => 'Visa',
        'DN' => 'Diners',
        'UN' => 'UnionPay'
    ];

    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'UN', 'DN'];
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /**
         * making filter by allowed cards
         */
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach (self::CARD_TYPE_MAP as $code => $name) {
            if (in_array($code, $allowed) || !count($allowed)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }
}
