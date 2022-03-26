<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class MWPDataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $paymentInfo = $this->readPaymentModelArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        foreach ($additionalData as $key => $data) {
            if ($key === 'email') {
                continue;
            }
            $paymentInfo->setAdditionalInformation($key, $data);
        }
    }
}
