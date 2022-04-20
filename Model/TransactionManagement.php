<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterfaceFactory;
use MerchantWarrior\Payment\Api\TransactionDetailDataRepositoryInterface;
use MerchantWarrior\Payment\Model\ResourceModel\TransactionDetail;

class TransactionManagement
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
     * @var TransactionDetail
     */
    private TransactionDetail $transactionDetail;

    /**
     * @param TransactionDetail $transactionDetail
     * @param TransactionDetailDataInterfaceFactory $transactionDetailFactory
     * @param TransactionDetailDataRepositoryInterface $transactionDetailRepository
     */
    public function __construct(
        TransactionDetail $transactionDetail,
        TransactionDetailDataInterfaceFactory $transactionDetailFactory,
        TransactionDetailDataRepositoryInterface $transactionDetailRepository
    ) {
        $this->transactionDetail = $transactionDetail;
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
     * @throws AlreadyExistsException
     */
    public function create(
        string $orderIncrementId,
        string $transactionId,
        int $status = TransactionDetailDataInterface::STATUS_NEW
    ): void {
        $transactionDetail = $this->getTransactionDetailInstance($orderIncrementId);
        if (!$transactionDetail->getId()) {
            $transactionDetail->setOrderId($orderIncrementId);
            $transactionDetail->setStatus($status);
            $transactionDetail->setTransactionId($transactionId);

            $this->transactionDetail->save($transactionDetail);
        }
    }

    /**
     * Get transaction
     *
     * @param string $orderIncrementId
     *
     * @return TransactionDetailDataInterface|null
     */
    public function getTransaction(string $orderIncrementId): ?TransactionDetailDataInterface
    {
        try {
            return $this->transactionDetailRepository->getByOrderId($orderIncrementId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Change transaction status
     *
     * @param string $orderId
     * @param int $status
     *
     * @return void
     */
    public function changeStatus(string $orderId, int $status): void
    {
        $this->transactionDetail->changeStatus([$orderId], $status);
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
