<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source;
use MerchantWarrior\Payment\Model\Config;

/**
 * Class GetPaymentIconsList
 *
 * Return list of icons by payment types
 */
class GetPaymentIconsList
{
    /**#@+
     * Module Name
     */
    public const MODULE_CODE = 'MerchantWarrior_Payment';
    /**#@-*/

    /**
     * @var Repository
     */
    private Repository $assetRepo;

    /**
     * @var Source
     */
    private Source $assetSource;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * GetPaymentIconsList constructor.
     *
     * @param Repository $assetRepo
     * @param Source $assetSource
     * @param Config $config
     */
    public function __construct(
        Repository $assetRepo,
        Source $assetSource,
        Config $config
    ) {
        $this->assetRepo = $assetRepo;
        $this->assetSource = $assetSource;
        $this->config = $config;
    }

    /**
     * Get module version
     *
     * @return string
     */
    /**
     * Form icons list
     *
     * @return array
     */
    public function execute(): array
    {
        $icons = [];
        foreach ($this->config->getCcTypes() as $ccType) {
            $icons[$ccType['code_alt']] = $this->getIconUrl($ccType['code_alt']);
        }
        return $icons;
    }

    /**
     * Get icon by CC Type
     *
     * @param string $ccType
     *
     * @return string|null
     */
    private function getIconUrl(string $ccType): ?string
    {
        try {
            $asset = $this->assetRepo->createAsset(self::MODULE_CODE . '::images/' . $ccType . '.png');
            if ($this->assetSource->findSource($asset)) {
                return $asset->getUrl();
            }
            return null;
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
