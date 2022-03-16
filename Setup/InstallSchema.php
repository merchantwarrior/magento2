<?php

namespace MerchantWarrior\Payment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $installer
     * @param ModuleContextInterface $context
     * @return void
     * @throws Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $installer,
        ModuleContextInterface $context
    ) {
        $installer->startSetup();
        $installer->getConnection()->dropTable($installer->getTable('merchant_warrior_payment_token'));
        $table = $installer->getTable('merchant_warrior_payment_token');
        if ($installer->getConnection()->isTableExists($table) != true) {
            $table = $installer->getConnection()->newTable($table)
                ->addColumn(
                    'token_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Token Id'
                )
                ->addColumn(
                    'created_date',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Created Date'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Customer Id'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Order Id'
                )
                ->addColumn(
                    'quote_id',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Quote Id'
                )
                ->addColumn(
                    'payment_token',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Payment Token'
                )
                ->addColumn(
                    'customer_email',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Customer Email'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'card_default',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Cart Default'
                )
                ->addColumn(
                    'card_expire',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Cart Expire'
                )
                ->addColumn(
                    'card_type',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Cart Type'
                )
                ->addColumn(
                    'cc_last4',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Cc Last 4'
                )
                ->addColumn(
                    'payment_type',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Payment Type'
                )
                ->addColumn(
                    'updated_date',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Updated Date'
                )
                ->addColumn(
                    'card_expiry_date',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Card Expiry Date'
                )
                ->addColumn(
                    'address_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Address ID'
                )
                ->addColumn(
                    'reference_number',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Reference Number'
                )
                ->addColumn(
                    'authorize_only',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true,
                        'default' => 0
                    ],
                    'Authorize Only'
                )
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Transaction ID'
                )
                ->addColumn(
                    'cc_number',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'CC Number'
                )
                ->setComment('Token Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
