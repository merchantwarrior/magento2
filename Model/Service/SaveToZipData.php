<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Driver\File;
use MerchantWarrior\Payment\Model\Config;

/**
 * Class SaveToZipData
 */
class SaveToZipData
{
    /**
     * @var DriverInterface
     */
    private $file;

    /**
     * @var Config
     */
    private $config;

    /**
     * SaveToZipData constructor.
     *
     * @param File $file
     * @param Config $config
     */
    public function __construct(
        File $file,
        Config $config
    ) {
        $this->file = $file;
        $this->config = $config;
    }

    /**
     * Save content to ZIP file
     *
     * @param string $fileName
     * @param string $content
     *
     * @return string|null
     */
    public function execute(string $fileName, string $content): ?string
    {
        $directory = $this->config->getSettlementDir();
        try {
            if (!$this->file->isExists($this->config->getSettlementDir())) {
                $this->file->createDirectory($this->config->getSettlementDir());
            }
            $this->file->filePutContents($directory . $fileName . '.zip', $content);

            return $directory . $fileName . '.zip';
        } catch (FileSystemException $exception) {
            return null;
        }
    }
}
