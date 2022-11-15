<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Api\TransactionDetailDataRepositoryInterface;
use MerchantWarrior\Payment\Model\TransactionManagement;

/**
 * The service to cancel an order and void authorization transaction.
 */
class RollbackTransaction
{
    /**
     * @var ProcessVoidInterface
     */
    private $processVoid;

    /**
     * @var TransactionDetailDataRepositoryInterface
     */
    private $transactionDetailRepository;

    /**
     * @var TransactionManagement
     */
    private $transactionManagement;

    /**
     * @param ProcessVoidInterface $processVoid
     * @param TransactionDetailDataRepositoryInterface $transactionDetailRepository
     * @param TransactionManagement $transactionManagement
     */
    public function __construct(
        ProcessVoidInterface $processVoid,
        TransactionDetailDataRepositoryInterface $transactionDetailRepository,
        TransactionManagement $transactionManagement
    ) {
        $this->processVoid = $processVoid;
        $this->transactionDetailRepository = $transactionDetailRepository;
        $this->transactionManagement = $transactionManagement;
    }

    /**
     * Cancels an order and authorization transaction.
     *
     * @param string $incrementId
     *
     * @return bool
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function execute(string $incrementId): bool
    {
        if (!$transaction = $this->getTransaction($incrementId)) {
            return false;
        }

        $this->processVoid->execute($transaction->getTransactionId());

        $this->transactionManagement->changeStatus(
            $transaction->getTransactionId(),
            TransactionDetailDataInterface::STATUS_FAILED
        );
        return true;
    }

    /**
     * Get transaction
     *
     * @param string $incrementId
     *
     * @return TransactionDetailDataInterface|null
     */
    private function getTransaction(string $incrementId): ?TransactionDetailDataInterface
    {
        try {
            return $this->transactionDetailRepository->getByOrderId($incrementId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
