<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class MerchantWarriorCronjob extends MerchantWarriorBase
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/merchant_warrior/cronjob.log';

    /**
     * @var int
     */
    protected $loggerType = MerchantWarriorLogger::MERCHANT_WARRIOR_NOTIFICATION_CRONJOB;

    protected $level = MerchantWarriorLogger::MERCHANT_WARRIOR_NOTIFICATION_CRONJOB;
}
