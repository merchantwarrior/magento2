<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Archive\Zip;
use Magento\Framework\File\Csv;
use MerchantWarrior\Payment\Api\Direct\GetSettlementInterface;
use MerchantWarrior\Payment\Model\Config;

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
     * GetSettlementData constructor.
     *
     * @param Csv $csv
     * @param Zip $zip
     * @param File $file
     * @param Config $config
     * @param GetSettlementInterface $getSettlement
     */
    public function __construct(
        Csv $csv,
        Zip $zip,
        File $file,
        Config $config,
        GetSettlementInterface $getSettlement
    ) {
        $this->csv = $csv;
        $this->zip = $zip;
        $this->file = $file;
        $this->config = $config;
        $this->getSettlement = $getSettlement;
    }

    /**
     * Get transactions list
     *
     * @return array
     */
    public function execute(string $settlementFrom, string $settlementTo): array
    {
        $directory = $this->config->getSettlementDir();

        $zipData = $this->getZipFile($directory, $settlementFrom, $settlementTo);

        $csvFile = $this->zip->unpack(
            $zipData,
            $directory . $settlementFrom . '-' . $settlementTo . '.csv',
        );
        $transactionList = $this->mapTransactions($this->csv->getData($csvFile));
        $this->file->deleteFile($csvFile);

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
        for ($i = 0; $i <= count($transactions); $i++) {
            $mapped[] = [
                'transaction_id' => $transactions[$i][0],
                'status' => $transactions[$i][1],
                'order_id' => $transactions[$i][4]
            ];
        }
        return $mapped;
    }

    /**
     * Get Zip File and is not exists create it
     *
     * @param string $directory
     * @param string $settlementFrom
     * @param string $settlementTo
     *
     * @return string|null
     */
    private function getZipFile(string $directory, string $settlementFrom, string $settlementTo): ?string
    {
        try {
            if (!$this->file->isExists($directory . $settlementFrom . '-' . $settlementTo . '.zip')) {
                return $this->getSettlement->execute($settlementFrom, $settlementTo);
            }
            return $directory . $settlementFrom . '-' . $settlementTo . '.zip';
        } catch (\Exception $e) {
            return null;
        }
    }
}
