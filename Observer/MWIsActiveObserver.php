<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use MerchantWarrior\Payment\Model\Service\IsCurrencyAllowed;

class MWIsActiveObserver extends AbstractDataAssignObserver
{
    /**
     * @var IsCurrencyAllowed
     */
    private $isCurrencyAllowed;

    /**
     * @param IsCurrencyAllowed $isCurrencyAllowed
     */
    public function __construct(
        IsCurrencyAllowed $isCurrencyAllowed
    ) {
        $this->isCurrencyAllowed = $isCurrencyAllowed;
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

        if (!$this->isCurrencyAllowed->execute($mInstance->getCode())) {
            $result->setData('is_available', false);
        }
    }
}
