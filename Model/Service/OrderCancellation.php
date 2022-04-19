<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Api\TransactionDetailDataRepositoryInterface;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

/**
 * The service to cancel an order and void authorization transaction.
 */
class OrderCancellation
{
    /**
     * @var ProcessVoidInterface
     */
    private ProcessVoidInterface $processVoid;

    /**
     * @var TransactionDetailDataRepositoryInterface
     */
    private TransactionDetailDataRepositoryInterface $transactionDetailDataRepository;

    /**
     * @var MerchantWarriorLogger
     */
    private MerchantWarriorLogger $merchantWarriorLogger;

    /**
     * @param ProcessVoidInterface $processVoid
     * @param TransactionDetailDataRepositoryInterface $transactionDetailDataRepository
     * @param MerchantWarriorLogger $merchantWarriorLogger
     */
    public function __construct(
        ProcessVoidInterface $processVoid,
        TransactionDetailDataRepositoryInterface $transactionDetailDataRepository,
        MerchantWarriorLogger $merchantWarriorLogger
    ) {
        $this->processVoid = $processVoid;
        $this->transactionDetailDataRepository = $transactionDetailDataRepository;
        $this->merchantWarriorLogger = $merchantWarriorLogger;
    }

    /**
     * Cancels an order and authorization transaction.
     *
     * @param string $incrementId
     *
     * @return bool
     */
    public function execute(string $incrementId): bool
    {
        if ($transaction = $this->getTransaction($incrementId)) {
            try {
                $this->processVoid->execute($transaction->getTransactionId());

                $transaction->setStatus(TransactionDetailDataInterface::STATUS_FAILED);
                $this->transactionDetailDataRepository->save($transaction);
            } catch (LocalizedException $e) {
                $this->merchantWarriorLogger->error($e->getMessage());
            }
        }
        return true;
    }

    /**
     * Get transaction
     *
     * @param string $incrementId
     *
     * @return null|TransactionDetailDataInterface
     */
    private function getTransaction(string $incrementId): ?TransactionDetailDataInterface
    {
        try {
            $transaction = $this->transactionDetailDataRepository->getByOrderId($incrementId);
            if ($transaction->getStatus() === TransactionDetailDataInterface::STATUS_NEW) {
                return $transaction;
            }
            return null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
