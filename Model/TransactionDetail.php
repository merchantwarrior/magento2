<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Model\ResourceModel\TransactionDetail as TransactionDetailResource;

class TransactionDetail extends AbstractModel implements TransactionDetailDataInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init(TransactionDetailResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return (int)$this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): int
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId(): string
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function getTransactionId(): string
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(int $status): TransactionDetailDataInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId(string $orderId): TransactionDetailDataInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritdoc
     */
    public function setTransactionId($transactionId): TransactionDetailDataInterface
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }
}
