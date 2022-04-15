<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class GetModuleVersion
 * Return current module version
 */
class GetModuleVersion
{
    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var ComponentRegistrarInterface
     */
    private ComponentRegistrarInterface $componentRegistrar;

    /**
     * GetModuleVersion constructor.
     *
     * @param File $driver
     * @param SerializerInterface $serializer
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        File $driver,
        SerializerInterface $serializer,
        ComponentRegistrarInterface $componentRegistrar
    ) {
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Get module version
     *
     * @return string
     */
    public function execute(): string
    {
        if ($data = $this->getJsonFileData()) {
            $composerJson = $this->serializer->unserialize($data);
            if (empty($composerJson['version'])) {
                return "Version is not available in composer.json";
            }
            return $composerJson['version'];
        }
        return "Version is not available in composer.json";
    }

    /**
     * Get json file data
     *
     * @return string|null
     */
    private function getJsonFileData(): ?string
    {
        $moduleDir = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            'MerchantWarrior_Payment'
        );

        $path = $moduleDir . '/composer.json';
        try {
            if ($this->driver->isExists($path)) {
                return $this->driver->fileGetContents($path);
            }
        } catch (FileSystemException $e) {
            return null;
        }
        return null;
    }
}
