<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Framework\DataObject;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;
use Monolog\Utils;

class Debugger
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var MerchantWarriorLogger
     */
    private MerchantWarriorLogger $logger;

    /**
     * @var DataObject
     */
    private DataObject $debugData;

    /**
     * DebugObserver constructor.
     *
     * @param Config $config
     * @param MerchantWarriorLogger $logger
     */
    public function __construct(
        Config $config,
        MerchantWarriorLogger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->debugData = new DataObject();
    }

    /**
     * Add debug data
     *
     * @param string $key
     * @param string|array|int $value
     *
     * @return Debugger
     */
    public function addDataByKey(string $key, $value): Debugger
    {
        $this->debugData->setData($key, $value);

        return $this;
    }

    /**
     * Set data
     *
     * @param array<mixed> $data
     *
     * @return $this
     */
    public function setData(array $data): Debugger
    {
        $data = $this->clearData($data);

        $this->debugData->addData($data);

        return $this;
    }

    /**
     * Save debug session
     *
     * @return void
     */
    public function execute(): void
    {
        if ($this->config->isDebuggerEnabled()) {
            $this->logger->addMerchantWarriorDebug('Start Log: ==================');

            if ($this->debugData->hasData('responseCode')) {
                $this->logger->addMerchantWarriorDebug('Response Code: ' . $this->debugData->getData('responseCode'));
                $this->debugData->unsetData('responseCode');
            }

            if ($this->debugData->hasData('responseMessage')) {
                $this->logger->addMerchantWarriorDebug('Response Message: ' . $this->debugData->getData('responseMessage'));
                $this->debugData->unsetData('responseMessage');
            }

            $this->logger->addMerchantWarriorDebug(
                Utils::jsonEncode($this->debugData->toArray(), JSON_PRETTY_PRINT)
            );
            $this->logger->addMerchantWarriorDebug('End Log: ====================');
        }

        $this->debugData->unsetData();
    }

    /**
     * Get formatted time
     *
     * @param int|null $callTime
     *
     * @return string
     */
    private function getFormattedDate(?int $callTime): ?string
    {
        if ($callTime) {
            $date = new \DateTime();
            return $date->setTimestamp($callTime)->format('Y-m-d H:i:s');
        }
        return null;
    }

    /**
     * Clear data from secure info
     *
     * @param array $data
     *
     * @return array
     */
    private function clearData(array $data): array
    {
        if (isset($data['passed_data']['apiKey'])) {
            $data['passed_data']['apiKey'] = '*****';
        }
        if (isset($data['call_time'])) {
            $data['call_time'] = $this->getFormattedDate($data['call_time']);
        }
        return $data;
    }
}
