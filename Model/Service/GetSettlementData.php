<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Archive\Zip;
use Magento\Framework\File\Csv;
use MerchantWarrior\Payment\Api\Direct\GetSettlementInterface;
use MerchantWarrior\Payment\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Class GetModuleVersion
 * Return current module version
 */
class GetSettlementData
{
    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var Zip
     */
    private $zip;

    /**
     * @var DriverInterface
     */
    private $file;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetSettlementInterface
     */
    private $getSettlement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetSettlementData constructor.
     *
     * @param Csv $csv
     * @param Zip $zip
     * @param File $file
     * @param Config $config
     * @param GetSettlementInterface $getSettlement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Csv $csv,
        Zip $zip,
        File $file,
        Config $config,
        GetSettlementInterface $getSettlement,
        LoggerInterface $logger
    ) {
        $this->csv = $csv;
        $this->zip = $zip;
        $this->file = $file;
        $this->config = $config;
        $this->getSettlement = $getSettlement;
        $this->logger = $logger;
    }

    /**
     * Get transactions list
     *
     * @param string $settlementFrom
     * @param string $settlementTo
     *
     * @return array
     */
    public function execute(string $settlementFrom, string $settlementTo): array
    {
        $fileName = $this->config->getSettlementDir() . $settlementFrom . '-' . $settlementTo;
        if (!$zipData = $this->getZipFile($fileName, $settlementFrom, $settlementTo)) {
            return [];
        }

        try {
            $csvFile = $this->zip->unpack($zipData, $fileName . '.csv');
            $transactionList = $this->mapTransactions($this->csv->getData($csvFile));

            $this->file->deleteFile($fileName . '.csv');
            $this->file->deleteFile($fileName . '.zip');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
        return $transactionList;
    }

    /**
     * Map returned transaction
     *
     * @param array $transactions
     *
     * @return array
     */
    private function mapTransactions(array $transactions): array
    {
        unset($transactions[0]);

        $mapped = [];
        for ($i = 1; $i <= count($transactions); $i++) {
            $transactionID = $transactions[$i][0];
            $orderID       = str_replace('ORDER_ID ', '', $transactions[$i][4]);

            if (isset($mapped[$orderID])) {
                $mapped[$orderID]['statuses'][] = $transactions[$i][1];
                $mapped[$orderID]['transactions'][] = $transactionID;
            } else {
                $mapped[$orderID] = [
                    'transactions' => [
                        $transactionID
                    ],
                    'statuses' => [
                        $transactions[$i][1]
                    ]
                ];
            }
        }
        return $mapped;
    }

    /**
     * Get Zip File and is not exists create it
     *
     * @param string $fileName
     * @param string $settlementFrom
     * @param string $settlementTo
     *
     * @return string|null
     */
    private function getZipFile(string $fileName, string $settlementFrom, string $settlementTo): ?string
    {
        try {
            if (!$this->file->isExists($fileName . '.zip')) {
                return $this->getSettlement->execute($settlementFrom, $settlementTo);
            }
            return $fileName . '.zip';
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }
}
