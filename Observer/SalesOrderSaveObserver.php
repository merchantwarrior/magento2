<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use MerchantWarrior\Payment\Model\Service\CreateTransaction;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;

class SalesOrderSaveObserver implements ObserverInterface
{
    /**
     * @var CreateTransaction
     */
    private CreateTransaction $createTransaction;

    /**
     * SalesOrderPlaceObserver constructor.
     *
     * @param CreateTransaction $createTransaction
     */
    public function __construct(
        CreateTransaction $createTransaction
    ) {
        $this->createTransaction = $createTransaction;
    }

    /**
     * Save additional transaction information for braintree methods
     *
     * @param Observer $observer
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');

        if (!$order->getId()) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if (0 === strpos($paymentMethod, ConfigProvider::METHOD_CODE)) {
            $additionalInformation = $order->getPayment()->getAdditionalInformation();
            if (!empty($additionalInformation['transactionID'])) {
                $this->createTransaction->execute($order->getIncrementId(), $additionalInformation['transactionID']);
            }
        }
    }
}
