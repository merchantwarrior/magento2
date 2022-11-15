<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Store\Model\StoreManagerInterface;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider as MWConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as MWPayFrameConfigProvider;

/**
 * Class IsCurrencyAllowed
 */
class IsCurrencyAllowed
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * Check is currency allowed constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Check is currency allowed
     *
     * @param string $paymentMethodCode
     * @param string $currency
     *
     * @return bool
     */
    public function execute(string $paymentMethodCode, string $currency = ''): bool
    {
        $allowedCurrency = $this->config->getAllowedCurrencies();
        if (!$this->isMWPayment($paymentMethodCode) || !count($allowedCurrency)) {
            return true;
        }

        if (empty($currency)) {
            $currency = $this->getCurrency();
        }

        if ((!in_array($currency, $allowedCurrency))) {
            return false;
        }
        return true;
    }

    /**
     * Check is Payment method is MW
     *
     * @param string $paymentMethodCode
     *
     * @return bool
     */
    private function isMWPayment(string $paymentMethodCode): bool
    {
        return in_array(
            $paymentMethodCode,
            [
                MWConfigProvider::METHOD_CODE,
                MWPayFrameConfigProvider::METHOD_CODE
            ],
            true
        );
    }

    /**
     * Get currency Code
     *
     * @return string|null
     */
    private function getCurrency(): ?string
    {
        try {
            return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        } catch (\Exception $e) {
            return null;
        }
    }
}
