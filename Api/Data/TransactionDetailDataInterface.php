<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Data;

/**
 * Interface TransactionDetail
 **/
interface TransactionDetailDataInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const STATUS = 'status';
    const TRANSACTION_ID = 'transaction_id';

    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Set ID
     *
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id): TransactionDetailDataInterface;

    /**
     * Set Status
     *
     * @param int $status
     *
     * @return self
     */
    public function setStatus(int $status): TransactionDetailDataInterface;

    /**
     * Set Order ID
     *
     * @param int $orderId
     *
     * @return self
     */
    public function setOrderId(int $orderId): TransactionDetailDataInterface;

    /**
     * Set transaction Source
     *
     * @param string $transactionId
     *
     * @return self
     */
    public function setTransactionId(string $transactionId): TransactionDetailDataInterface;
}
