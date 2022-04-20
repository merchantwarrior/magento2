<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;

/**
 * Interface TransactionDetailDataRepositoryInterface
 */
interface TransactionDetailDataRepositoryInterface
{
    /**
     * Create transaction row
     *
     * @param TransactionDetailDataInterface $data
     *
     * @return TransactionDetailDataInterface
     * @throws InputException
     * @throws StateException
     * @throws CouldNotSaveException
     */
    public function save(TransactionDetailDataInterface $data): TransactionDetailDataInterface;

    /**
     * Get transaction by ID
     *
     * @param int $entityId
     * @param bool $forceReload
     *
     * @return TransactionDetailDataInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId, bool $forceReload = false): TransactionDetailDataInterface;

    /**
     * Get transaction by order ID
     *
     * @param string $orderId
     *
     * @return TransactionDetailDataInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId(string $orderId): TransactionDetailDataInterface;

    /**
     * Get transaction by transaction ID
     *
     * @param string $transactionId
     *
     * @return TransactionDetailDataInterface
     * @throws NoSuchEntityException
     */
    public function getByTransactionId(string $transactionId): TransactionDetailDataInterface;

    /**
     * Delete transaction row
     *
     * @param TransactionDetailDataInterface $data
     *
     * @return bool Will returned True if deleted
     * @throws StateException
     * @throws CouldNotDeleteException
     */
    public function delete(TransactionDetailDataInterface $data): bool;

    /**
     * Delete by ID
     *
     * @param int $entityId
     *
     * @return bool Will returned True if deleted
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function deleteById(int $entityId): bool;
}
