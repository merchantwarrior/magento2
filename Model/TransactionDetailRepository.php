<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model;

use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterfaceFactory;
use MerchantWarrior\Payment\Model\ResourceModel\TransactionDetail;
use MerchantWarrior\Payment\Api\TransactionDetailDataRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Transaction Detail Repository
 */
class TransactionDetailRepository implements TransactionDetailDataRepositoryInterface
{
    /**
     * @var TransactionDetail
     */
    private $transactionDetailResource;

    /**
     * @var TransactionDetailDataInterfaceFactory
     */
    private $transactionDetailResourceFactory;

    /**
     * @var array
     */
    private $instancesById = [];

    /**
     * @param TransactionDetail $transactionDetailResource
     * @param TransactionDetailDataInterfaceFactory $transactionDetailResourceFactory
     */
    public function __construct(
        TransactionDetail $transactionDetailResource,
        TransactionDetailDataInterfaceFactory $transactionDetailResourceFactory
    ) {
        $this->transactionDetailResource = $transactionDetailResource;
        $this->transactionDetailResourceFactory = $transactionDetailResourceFactory;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $entityId, bool $forceReload = false): TransactionDetailDataInterface
    {
        if (!isset($this->instancesById[$entityId]) || $forceReload) {
            $data = $this->transactionDetailResourceFactory->create();
            $this->transactionDetailResource->load($data, $entityId);
            if (!$data->getId()) {
                throw new NoSuchEntityException(
                    __('The transaction with the "%1" ID doesn\'t exist.', $data)
                );
            }
            $this->cacheData($data, TransactionDetailDataInterface::ENTITY_ID);
        }
        return $this->instancesById[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getByOrderId(string $orderId): TransactionDetailDataInterface
    {
        $data = $this->transactionDetailResourceFactory->create();
        $this->transactionDetailResource->load($data, $orderId, TransactionDetailDataInterface::ORDER_ID);
        if (!$data->getId()) {
            throw new NoSuchEntityException(
                __('The transaction with the "%1" ID doesn\'t exist.', $data)
            );
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getByTransactionId(string $transactionId): TransactionDetailDataInterface
    {
        $data = $this->transactionDetailResourceFactory->create();
        $this->transactionDetailResource->load(
            $data,
            $transactionId,
            TransactionDetailDataInterface::TRANSACTION_ID
        );
        if (!$data->getId()) {
            throw new NoSuchEntityException(
                __('The transaction with the "%1" ID doesn\'t exist.', $data)
            );
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function save(TransactionDetailDataInterface $data): TransactionDetailDataInterface
    {
        try {
            $this->transactionDetailResource->save($data);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function delete(TransactionDetailDataInterface $data): bool
    {
        try {
            $this->transactionDetailResource->delete($data);

            return true;
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * Add cache to internal cache.
     *
     * @param TransactionDetailDataInterface $data
     * @param string $key
     *
     * @return void
     */
    private function cacheData(TransactionDetailDataInterface $data, string $key): void
    {
        $this->instancesById[$data->getData($key)] = $data;
    }
}
