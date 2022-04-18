<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterfaceFactory;
use Magento\Sales\Model\Order;

class SalesOrderSaveObserver implements ObserverInterface
{
    /**
     * @var TransactionDetailDataInterfaceFactory
     */
    protected $transactionDetailFactory;

    /**
     * SalesOrderPlaceObserver constructor.
     * @param TransactionDetailDataInterfaceFactory $transactionDetailFactory
     */
    public function __construct(
        TransactionDetailDataInterfaceFactory $transactionDetailFactory
    ) {
        $this->transactionDetailFactory = $transactionDetailFactory;
    }

    /**
     * Save additional transaction information for braintree methods
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');

        if (!$order->getId()) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if (0 === strpos($paymentMethod, 'braintree')) {
            $additionalInformation = $order->getPayment()->getAdditionalInformation();
            if (!empty($additionalInformation['transactionID'])) {
                /** @var TransactionDetailDataInterface $transactionDetail */
                $transactionDetail = $this->transactionDetailFactory->create();

                // $order-isObjectNew is always false. Workaround: ensure no entries are added if one exists already
                $transactionDetail->getResource()->load($transactionDetail, $order->getId(), 'order_id');
                if (!$transactionDetail->getId()) {
                    $transactionDetail->setOrderId($order->getId());
                    $transactionDetail->setStatus(TransactionDetailDataInterface::STATUS_NEW);
                    $transactionDetail->setTransactionId(
                        $additionalInformation['transactionID']
                    );
                    $transactionDetail->getResource()->save($transactionDetail);
                }
            }
        }
    }
}
