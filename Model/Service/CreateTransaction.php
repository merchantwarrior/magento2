<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterfaceFactory;
use MerchantWarrior\Payment\Api\TransactionDetailDataRepositoryInterface;

/**
 * The service creating a new transaction
 */
class CreateTransaction
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
     * @param string $orderIncrementId
     * @param string $transactionId
     * @param int $status
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function execute(
        string $orderIncrementId,
        string $transactionId,
        int $status = TransactionDetailDataInterface::STATUS_NEW
    ) {
        $transactionDetail = $this->getTransactionDetailInstance($orderIncrementId);
        if (!$transactionDetail->getId()) {
            $transactionDetail->setOrderId($orderIncrementId);
            $transactionDetail->setStatus($status);
            $transactionDetail->setTransactionId($transactionId);

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
