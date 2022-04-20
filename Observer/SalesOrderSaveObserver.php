<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Model\TransactionManagement;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;

class SalesOrderSaveObserver implements ObserverInterface
{
    /**
     * @var TransactionManagement
     */
    private TransactionManagement $transactionManagement;

    /**
     * SalesOrderPlaceObserver constructor.
     *
     * @param TransactionManagement $transactionManagement
     */
    public function __construct(
        TransactionManagement $transactionManagement
    ) {
        $this->transactionManagement = $transactionManagement;
    }

    /**
     * Save additional transaction information for braintree methods
     *
     * @param Observer $observer
     *
     * @return void
     * @throws AlreadyExistsException
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
            $this->createTransactionRow($order);
        }
    }

    /**
     * Create transaction row
     *
     * @param OrderInterface $order
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function createTransactionRow(OrderInterface $order): void
    {
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if (!empty($additionalInformation['transactionID'])) {
            if ($this->transactionManagement->getTransaction($order->getIncrementId())) {
                $this->transactionManagement->changeStatus(
                    $order->getIncrementId(),
                    $this->getTransactionStatus($order)
                );
            } else {
                $this->transactionManagement->create(
                    $order->getIncrementId(),
                    $additionalInformation['transactionID'],
                    $this->getTransactionStatus($order)
                );
            }
        }
    }

    /**
     * Get transaction status
     *
     * @param OrderInterface $order
     *
     * @return int
     */
    private function getTransactionStatus(OrderInterface $order): int
    {
        $invoices = $order->getInvoiceCollection();
        if (!$invoices->count()) {
            return TransactionDetailDataInterface::STATUS_NEW;
        }

        $status = TransactionDetailDataInterface::STATUS_SUCCESS;
        foreach ($invoices as $invoice) {
            if ($invoice->getState() !== Invoice::STATE_PAID) {
                $status = TransactionDetailDataInterface::STATUS_NEW;
            }
        }
        return $status;
    }
}
