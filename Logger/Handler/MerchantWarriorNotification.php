<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class MerchantWarriorNotification extends MerchantWarriorBase
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/merchant_warrior/notification.log';

    /**
     * @var int
     */
    protected $loggerType = MerchantWarriorLogger::MERCHANT_WARRIOR_NOTIFICATION;

    protected $level = MerchantWarriorLogger::MERCHANT_WARRIOR_NOTIFICATION;
}
