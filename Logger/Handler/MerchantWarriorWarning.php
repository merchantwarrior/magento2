<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class MerchantWarriorWarning extends MerchantWarriorBase
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/merchant_warrior/warning.log';

    /**
     * @var int
     */
    protected $loggerType = MerchantWarriorLogger::MERCHANT_WARRIOR_WARNING;

    protected $level = MerchantWarriorLogger::MERCHANT_WARRIOR_WARNING;
}
