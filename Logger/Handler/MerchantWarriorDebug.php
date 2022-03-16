<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class MerchantWarriorDebug extends MerchantWarriorBase
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/merchant_warrior/debug.log';

    /**
     * @var int
     */
    protected $loggerType = MerchantWarriorLogger::MERCHANT_WARRIOR_DEBUG;

    protected $level = MerchantWarriorLogger::MERCHANT_WARRIOR_DEBUG;
}
