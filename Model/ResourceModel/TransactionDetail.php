<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use MerchantWarrior\Payment\Api\Data\TransactionDetailDataInterface;

class TransactionDetail extends AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('merchant_warrior_transaction_details', 'entity_id');
    }

    /**
     * Change transaction status.
     *
     * @param int[] $ids
     * @param int $status
     *
     * @return void
     */
    public function changeStatus(array $ids, int $status): void
    {
        $this->getConnection()->update(
            $this->geTransactionTable(),
            [
                TransactionDetailDataInterface::STATUS => $status
            ],
            [
                TransactionDetailDataInterface::ORDER_ID . ' IN (?)' => $ids
            ]
        );
    }

    /**
     * Get name of table.
     *
     * @return string
     */
    protected function geTransactionTable(): string
    {
        return $this->getTable('merchant_warrior_transaction_details');
    }
}
