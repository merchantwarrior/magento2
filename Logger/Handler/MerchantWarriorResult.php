<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class MerchantWarriorResult extends MerchantWarriorBase
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/merchant_warrior/result.log';

    /**
     * @var int
     */
    protected $loggerType = MerchantWarriorLogger::MERCHANT_WARRIOR_RESULT;

    protected $level = MerchantWarriorLogger::MERCHANT_WARRIOR_RESULT;
}
