<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api\Data;

/**
 * Interface TransactionDetail
 **/
interface TransactionDetailDataInterface
{
    public const ENTITY_ID = 'entity_id';
    public const ORDER_ID = 'order_id';
    public const STATUS = 'status';
    public const TRANSACTION_ID = 'transaction_id';

    public const STATUS_NEW = 1;
    public const STATUS_SUCCESS = 2;
    public const STATUS_FAILED = 3;

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get oder ID
     *
     * @return string
     */
    public function getOrderId(): string;

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Return transaction ID
     *
     * @return string
     */
    public function getTransactionId(): string;

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
     * @param string $orderId
     *
     * @return self
     */
    public function setOrderId(string $orderId): TransactionDetailDataInterface;

    /**
     * Set transaction Source
     *
     * @param string $transactionId
     *
     * @return self
     */
    public function setTransactionId(string $transactionId): TransactionDetailDataInterface;
}
