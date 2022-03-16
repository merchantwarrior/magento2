<?php

namespace MerchantWarrior\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const MODULE_NAME = 'merchant-warrior-magento2';

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar
    ) {
        parent::__construct($context);
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Get Merchant Warrior module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return self::MODULE_NAME;
    }

    /**
     * Get Merchant Warrior magento module's version from composer.json
     *
     * @return string
     */
    public function getModuleVersion()
    {
        $moduleDir = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            'MerchantWarrior_Payment'
        );

        $composerJson = file_get_contents($moduleDir . '/composer.json');
        $composerJson = json_decode($composerJson, true);

        if (empty($composerJson['version'])) {
            return "Version is not available in composer.json";
        }

        return $composerJson['version'];
    }

    /**
     * Return recurring types for configuration setting
     *
     * @return array
     */
    public function getModes()
    {
        return [
            '1' => 'Test Mode',
            '0' => 'Production Mode'
        ];
    }

    /**
     * @return mixed
     */
    public function getMerchantWarriorCcTypes()
    {
        return $this->dataStorage->get('merchant_warrior_credit_cards');
    }
}
