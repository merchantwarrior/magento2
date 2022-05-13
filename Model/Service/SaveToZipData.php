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
    /**#@+
     * Debug directory
     */
    const DEBUG_DIRECTORY = DIRECTORY_SEPARATOR . 'debug' . DIRECTORY_SEPARATOR;
    /**#@-*/

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
        try {
            $directory = $this->getDir();

            $this->file->filePutContents($directory . $fileName . '.zip', $content);

            if ($this->config->isDebuggerEnabled()) {
                $this->file->copy(
                    $directory . $fileName . '.zip',
                    $directory . self::DEBUG_DIRECTORY . $fileName . '.zip'
                );
            }
            return $directory . $fileName . '.zip';
        } catch (FileSystemException $exception) {
            return null;
        }
    }

    /**
     * Get settlement dir
     *
     * @return string
     * @throws FileSystemException
     */
    private function getDir(): string
    {
        $directory = $this->config->getSettlementDir();
        if (!$this->file->isExists($directory)) {
            $this->file->createDirectory($directory);
        }

        if ($this->config->isDebuggerEnabled() && !$this->file->isExists($directory . self::DEBUG_DIRECTORY)) {
            $this->file->createDirectory($directory . self::DEBUG_DIRECTORY);
        }
        return $directory;
    }
}
