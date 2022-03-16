<?php

namespace MerchantWarrior\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

class PayFrameConfigProvider implements ConfigProviderInterface
{
    const CODE = 'merchant_warrior_payframe';
    const CC_VAULT_CODE = 'merchant_warrior_payframe_vault';

    public function getConfig()
    {
        // TODO: Implement getConfig() method.
    }
}