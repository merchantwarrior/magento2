<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MerchantWarrior\Payment\Model\Config;

/**
 * List of allowed Credit Cards
 */
class MerchantWarriorCcType implements OptionSourceInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->config->getCcTypes() as $code => $name) {
            $options[] = [
                'value' => $code,
                'label' => $name['name']
            ];
        }
        return $options;
    }
}
