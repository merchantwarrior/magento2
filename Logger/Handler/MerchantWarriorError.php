<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class MerchantWarriorError extends MerchantWarriorBase
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/merchant_warrior/error.log';

    /**
     * @var int
     */
    protected $loggerType = MerchantWarriorLogger::ERROR;

    /**
     * @var
     */
    protected $level = MerchantWarriorLogger::ERROR;
}
