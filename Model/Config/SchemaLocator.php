<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Merged config schema file name
     */
    public const MERGED_CONFIG_SCHEMA = 'merchant_warrior_payment_file.xsd';

    /**
     * Per file validation schema file name
     */
    public const PER_FILE_VALIDATION_SCHEMA = 'merchant_warrior_payment.xsd';

    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var null|string
     */
    protected ?string $schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var null|string
     */
    protected ?string $perFileSchema = null;

    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $etcDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'MerchantWarrior_Payment');
        $this->schema = $etcDir . '/' . self::MERGED_CONFIG_SCHEMA;
        $this->perFileSchema = $etcDir . '/' . self::PER_FILE_VALIDATION_SCHEMA;
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema(): ?string
    {
        return $this->perFileSchema;
    }
}
