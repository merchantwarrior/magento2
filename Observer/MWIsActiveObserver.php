<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Store\Model\StoreManagerInterface;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider as MWConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as MWPayFrameConfigProvider;

class MWIsActiveObserver extends AbstractDataAssignObserver
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $result = $observer->getData('result');
        $mInstance = $observer->getData('method_instance');
        if (in_array(
                $mInstance->getCode(),
                [
                    MWConfigProvider::METHOD_CODE,
                    MWPayFrameConfigProvider::METHOD_CODE
                ],
                true
            ) && !in_array($this->getCurrency(), ['AUD', 'NZD'])
        ) {
            $result->setData('is_available', false);
        }
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
