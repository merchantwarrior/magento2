<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class MerchantWarriorInfo extends MerchantWarriorBase
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/merchant_warrior/info.log';

    /**
     * @var int
     */
    protected $loggerType = MerchantWarriorLogger::INFO;

    protected $level = MerchantWarriorLogger::INFO;
}
