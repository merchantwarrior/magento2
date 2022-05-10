<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Framework\DataObject;
use MerchantWarrior\Payment\Api\Direct\GetSettlementInterface;
use Monolog\Utils;
use Psr\Log\LoggerInterface;

class Debugger
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DataObject
     */
    private $debugData;

    /**
     * DebugObserver constructor.
     *
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->debugData = new DataObject();
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
            $this->logger->debug('Start Log: ==================');

            if ($this->debugData->hasData('responseCode')) {
                $this->logger->debug('Response Code: ' . $this->debugData->getData('responseCode'));
                $this->debugData->unsetData('responseCode');
            }

            if ($this->debugData->hasData('responseMessage')) {
                $this->logger->debug('Response Message: ' . $this->debugData->getData('responseMessage'));
                $this->debugData->unsetData('responseMessage');
            }

            $this->logger->debug(
                Utils::jsonEncode($this->debugData->toArray(), JSON_PRETTY_PRINT)
            );
            $this->logger->debug('End Log: ====================');
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

        if (isset($data['response_data']['method'])
            && $data['response_data']['method'] == GetSettlementInterface::API_METHOD
        ) {
            $data['response_data']['mwResponse'] = '';
        }
        return $data;
    }
}
