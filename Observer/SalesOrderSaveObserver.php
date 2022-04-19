<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterfaceFactory;
use MerchantWarrior\Payment\Api\TransactionDetailDataRepositoryInterface;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;

class SalesOrderSaveObserver implements ObserverInterface
{
    /**
     * @var TransactionDetailDataInterfaceFactory
     */
    private TransactionDetailDataInterfaceFactory $transactionDetailFactory;

    /**
     * @var TransactionDetailDataRepositoryInterface
     */
    private TransactionDetailDataRepositoryInterface $transactionDetailRepository;

    /**
     * SalesOrderPlaceObserver constructor.
     *
     * @param TransactionDetailDataInterfaceFactory $transactionDetailFactory
     * @param TransactionDetailDataRepositoryInterface $transactionDetailRepository
     */
    public function __construct(
        TransactionDetailDataInterfaceFactory $transactionDetailFactory,
        TransactionDetailDataRepositoryInterface $transactionDetailRepository
    ) {
        $this->transactionDetailFactory = $transactionDetailFactory;
        $this->transactionDetailRepository = $transactionDetailRepository;
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
                $this->saveData($order->getIncrementId(), $additionalInformation);
            }
        }
    }

    /**
     * Save transaction data
     *
     * @param string $orderId
     * @param array $additionalInformation
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    private function saveData(string $orderId, array $additionalInformation): void
    {
        $transactionDetail = $this->getTransactionDetailInstance($orderId);
        if (!$transactionDetail->getId()) {
            $transactionDetail->setOrderId($orderId);
            $transactionDetail->setStatus(TransactionDetailDataInterface::STATUS_NEW);
            $transactionDetail->setTransactionId(
                $additionalInformation['transactionID']
            );

            $this->transactionDetailRepository->save($transactionDetail);
        }
    }

    /**
     * Get transaction detail
     *
     * @param string $orderId
     *
     * @return TransactionDetailDataInterface
     */
    private function getTransactionDetailInstance(string $orderId): TransactionDetailDataInterface
    {
        try {
            $transactionDetail = $this->transactionDetailRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $transactionDetail = $this->transactionDetailFactory->create();
        }
        return $transactionDetail;
    }
}
