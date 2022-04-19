<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\FlagManager;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;

/**
 * The service to cancel an order and void authorization transaction.
 */
class OrderCancellation
{
    /**
     * @var FlagManager
     */
    private FlagManager $flagManager;

    /**
     * @var ProcessVoidInterface
     */
    private ProcessVoidInterface $processVoid;

    /**
     * @var CreateTransaction
     */
    private CreateTransaction $createTransaction;

    /**
     * @param FlagManager $flagManager
     * @param ProcessVoidInterface $processVoid
     * @param CreateTransaction $createTransaction
     */
    public function __construct(
        FlagManager $flagManager,
        ProcessVoidInterface $processVoid,
        CreateTransaction $createTransaction
    ) {
        $this->flagManager = $flagManager;
        $this->processVoid = $processVoid;
        $this->createTransaction = $createTransaction;
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
        if ($transaction = $this->getTransactionId($incrementId)) {
            $this->processVoid->execute($transaction);

            $this->createTransaction->execute(
                $incrementId,
                $transaction,
                TransactionDetailDataInterface::STATUS_FAILED
            );

            $this->unsetTransactionId($incrementId);
        }
        return true;
    }

    /**
     * Get transaction ID
     *
     * @param string $orderId
     *
     * @return string|null
     */
    private function getTransactionId(string $orderId): ?string
    {
        $data = $this->flagManager->getFlagData('mw_transaction');
        if (count($data) && isset($data[$orderId])) {
            return $data[$orderId];
        }
        return null;
    }

    /**
     * Unset transaction ID
     *
     * @param string $orderId
     *
     * @return void
     */
    private function unsetTransactionId(string $orderId): void
    {
        $data = $this->flagManager->getFlagData('mw_transaction');
        if (count($data) && isset($data[$orderId])) {
            unset($data[$orderId]);

            $this->flagManager->saveFlag('mw_transaction', $data);
        }
    }
}
