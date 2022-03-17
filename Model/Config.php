<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    /**#@+
     * Configuration constants
     */
    const XML_PATH_IS_ENABLED = 'payment/merchant_warrior_payframe/active';
    const XML_PATH_IS_SANDBOX_MODE_ENABLED = 'payment/merchant_warrior_abstract/sandbox_mode';
    /**#@-*/

    /**#@+
     * Configuration for credentials
     */
    const XML_CREDENTIALS_MERCHANT_USER_ID = 'payment/merchant_warrior_abstract/merchant_uuid';
    const XML_CREDENTIALS_API_KEY = 'payment/merchant_warrior_abstract/api_key';
    const XML_CREDENTIALS_API_PASS_PHRASE = 'payment/merchant_warrior_abstract/api_passphrase';
    /**#@-*/

    /**#@+
     * Configuration debugger constants
     */
    const XML_DEBUGGER_IS_ENABLED = 'payment/merchant_warrior_abstract/debug';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
    }

    /**
     * Is module enabled and all credentials are filled
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        $isEnabled = (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($this->getPassPhrase() !== null)
            && ($this->getMerchantUserId() !== null)
            && ($this->getApiKey() !== null)
            && $isEnabled;
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
