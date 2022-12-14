<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Config\DataInterface;

class Config
{
    /**#@+
     * Count transaction days
     */
    const COUNT_SETTLEMENT_DAYS = 7;
    /**#@-*/

    /**#@+
     * MerchantWarrior Api URL
     */
    public const API_SANDBOX_URL = 'https://base.merchantwarrior.com/';
    public const API_LIVE_URL = 'https://api.merchantwarrior.com/';
    /**#@-*/

    /**#@+
     * Configuration constants
     */
    public const XML_PATH_ACTIVE = 'payment/merchant_warrior/active';
    public const XML_PATH_IS_3DS_ENABLED = 'payment/merchant_warrior/enable_3ds';
    public const XML_PATH_IS_SANDBOX_MODE_ENABLED = 'payment/merchant_warrior/sandbox_mode';
    public const XML_PATH_SETTLEMENT_DAYS = 'payment/merchant_warrior/settlement_days';
    /**#@-*/

    /**#@+
     * Configuration for credentials
     */
    public const XML_CREDENTIALS_MERCHANT_USER_ID = 'payment/merchant_warrior/merchant_uuid';
    public const XML_CREDENTIALS_API_KEY = 'payment/merchant_warrior/api_key';
    public const XML_CREDENTIALS_API_PASS_PHRASE = 'payment/merchant_warrior/api_passphrase';
    /**#@-*/

    /**#@+
     * Configuration for PayFrame constants
     */
    public const XML_PATH_PAYFRAME_ACTIVE = 'payment/merchant_warrior_payframe/active';
    public const XML_PATH_PAYFRAME_ALLOWED_CC = 'payment/merchant_warrior_payframe/cctypes';
    /**#@-*/

    /**#@+
     * Configuration for Direct API constants
     */
    public const XML_PATH_DIRECTAPI_ACTIVE = 'payment/merchant_warrior/active';
    public const XML_PATH_DIRECTAPI_ALLOWED_CC = 'payment/merchant_warrior/cctypes';
    /**#@-*/

    /**#@+
     * Advanced configurations
     */
    public const XML_PATH_ALLOWED_CURRENCY = 'payment/merchant_warrior/allow_currency';
    public const XML_PATH_ALLOWED_SPECIFIC = 'payment/merchant_warrior/allowspecific';
    public const XML_PATH_ALLOWED_SPECIFICCOUNTRY = 'payment/merchant_warrior/specificcountry';
    /**#@-*/

    /**#@+
     * Configuration debugger constants
     */
    public const XML_DEBUGGER_IS_ENABLED = 'payment/merchant_warrior/debug';
    /**#@-*/

    /**#@+
     * Configuration file directories
     */
    public const SETTLEMENT_DIR = 'merchant_warrior/settlement';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var DataInterface
     */
    protected $dataStorage;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param DataInterface $dataStorage
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        DataInterface $dataStorage,
        DirectoryList $directoryList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->dataStorage = $dataStorage;
        $this->directoryList = $directoryList;
    }

    /**
     * Is module enabled and all credentials are filled
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        $isEnabled = (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($this->getPassPhrase() !== null)
            && ($this->getMerchantUserId() !== null)
            && ($this->getApiKey() !== null)
            && $isEnabled;
    }

    /**
     * Is 3DS enabled
     *
     * @return boolean
     */
    public function is3dsEnabled(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_3DS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Is in test mode
     *
     * @return boolean
     */
    public function isSandBoxModeEnabled(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_SANDBOX_MODE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Merchant User ID
     *
     * @return null|string
     */
    public function getMerchantUserId(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_CREDENTIALS_MERCHANT_USER_ID,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get pass phrase
     *
     * @return null|string
     */
    public function getPassPhrase(): ?string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_CREDENTIALS_API_PASS_PHRASE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if ($value) {
            return $this->encryptor->decrypt($value);
        }
        return null;
    }

    /**
     * Get API key
     *
     * @return null|string
     */
    public function getApiKey(): ?string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_CREDENTIALS_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if ($value) {
            return $this->encryptor->decrypt($value);
        }
        return null;
    }

    /**
     * Check is Pay Frame method active
     *
     * @return bool
     */
    public function isPayFrameActive(): bool
    {
        $result = $this->scopeConfig->getValue(
            self::XML_PATH_PAYFRAME_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($this->isEnabled() && $result);
    }

    /**
     * Get list of allowed Credit Cards
     *
     * @return array
     */
    public function getPayFrameAllowedTypeCards(): array
    {
        return $this->getTypeCardsByConfigPath(self::XML_PATH_PAYFRAME_ALLOWED_CC);
    }

    /**
     * Get list of allowed Credit Cards for Admin DirectAPI method
     *
     * @return array
     */
    public function getAdminAllowedTypeCards(): array
    {
        return $this->getTypeCardsByConfigPath(self::XML_PATH_DIRECTAPI_ALLOWED_CC);
    }

    /**
     * Get allowed currency
     *
     * @return array
     */
    public function getAllowedCurrencies(): array
    {
        $currencies = $this->scopeConfig->getValue(
            Config::XML_PATH_ALLOWED_CURRENCY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if ($currencies) {
            return explode(',', $currencies);
        }
        return [];
    }

    /**
     * Get type cards
     *
     * @param string $configPath
     *
     * @return array
     */
    protected function getTypeCardsByConfigPath(string $configPath): array
    {
        $cards = $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if ($cards) {
            return explode(',', $cards);
        }
        return [];
    }

    /**
     * Is debugger
     *
     * @return boolean
     */
    public function isDebuggerEnabled(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_DEBUGGER_IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get settlement days
     *
     * @return int
     */
    public function getSettlementDays(): int
    {
        if (!$value = $this->scopeConfig->getValue(self::XML_PATH_SETTLEMENT_DAYS, ScopeInterface::SCOPE_STORE)) {
            return (int)self::COUNT_SETTLEMENT_DAYS;
        }
        return (int)$value;
    }

    /**
     * Get API Url
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return ($this->isSandBoxModeEnabled())
            ? self::API_SANDBOX_URL : self::API_LIVE_URL;
    }

    /**
     * Get list of credit card types
     *
     * @return array
     * @api
     */
    public function getCcTypes(): array
    {
        return $this->dataStorage->get('credit_cards');
    }

    /**
     * Get payment action
     *
     * @param string $paymentCode
     *
     * @return string
     */
    public function getPaymentAction(string $paymentCode): string
    {
        return $this->scopeConfig->getValue(
            'payment/' . $paymentCode . '/payment_action',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int
    {
        try {
            return (int)$this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            return (int)Store::DEFAULT_STORE_ID;
        }
    }

    /**
     * Get full path to directory with backups
     *
     * @return string
     */
    public function getSettlementDir(): string
    {
        try {
            return $this->directoryList->getPath(DirectoryList::VAR_DIR)
                . DIRECTORY_SEPARATOR
                . self::SETTLEMENT_DIR
                . DIRECTORY_SEPARATOR;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get config value
     *
     * @param string $path
     *
     * @return string|null
     */
    protected function getConfigValue(string $path): ?string
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
}
