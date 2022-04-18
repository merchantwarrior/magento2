<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

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
}
