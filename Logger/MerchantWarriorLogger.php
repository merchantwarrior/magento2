<?php

namespace MerchantWarrior\Payment\Logger;

use Exception;
use Monolog\Logger;

class MerchantWarriorLogger extends Logger
{
    /**
     * Detailed debug information
     */
    const MERCHANT_WARRIOR_DEBUG = 101;
    const MERCHANT_WARRIOR_NOTIFICATION = 201;
    const MERCHANT_WARRIOR_RESULT = 202;
    const MERCHANT_WARRIOR_NOTIFICATION_CRONJOB = 203;
    const MERCHANT_WARRIOR_WARNING = 301;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     * Overrule the default to add MerchantWarrior specific loggers to log into seperated files
     *
     * @var array $levels Logging levels
     */
    protected static $levels = [
        100 => 'DEBUG',
        101 => 'MERCHANT_WARRIOR_DEBUG',
        200 => 'INFO',
        201 => 'MERCHANT_WARRIOR_NOTIFICATION',
        202 => 'MERCHANT_WARRIOR_RESULT',
        203 => 'MERCHANT_WARRIOR_NOTIFICATION_CRONJOB',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    ];

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addMerchantWarriorNotification($message, array $context = [])
    {
        return $this->addRecord(static::MERCHANT_WARRIOR_NOTIFICATION, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return bool
     */
    public function addMerchantWarriorDebug($message, array $context = [])
    {
        return $this->addRecord(static::MERCHANT_WARRIOR_DEBUG, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return bool
     */
    public function addMerchantWarriorResult($message, array $context = [])
    {
        return $this->addRecord(static::MERCHANT_WARRIOR_RESULT, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return bool
     */
    public function addMerchantWarriorNotificationCronjob($message, array $context = [])
    {
        return $this->addRecord(static::MERCHANT_WARRIOR_NOTIFICATION_CRONJOB, $message, $context);
    }

    /**
     * Adds a log record.
     *
     * @param integer $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     *
     * @return Boolean Whether the record has been processed
     */
    public function addRecord(int $level, string $message, array $context = []): bool
    {
        $context['is_exception'] = $message instanceof Exception;
        return parent::addRecord($level, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addNotificationLog($message, array $context = [])
    {
        return $this->addRecord(static::INFO, $message, $context);
    }
}
