<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use MerchantWarrior\Payment\Api\Direct\GetSettlementInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as PFConfigProvider;
use Psr\Log\LoggerInterface;

/**
 * Class CancelStuckOrders
 */
class CancelStuckOrders
{
    /**#@+
     * Count orders const
     */
    const COUNT_PROCESSED_ORDERS = 50;
    /**#@-*/

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var GetSettlementData
     */
    private $getSettlementData;

    /**
     * @var Config
     */
    private $config;

    /**
     * CancelStuckOrders constructor.
     *
     * @param Collection $collection
     * @param OrderRepositoryInterface $orderRepository
     * @param GetSettlementData $getSettlementData
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Collection $collection,
        OrderRepositoryInterface $orderRepository,
        GetSettlementData $getSettlementData,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->collection = $collection;
        $this->orderRepository = $orderRepository;
        $this->getSettlementData = $getSettlementData;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Get module version
     *
     * @return void
     */
    public function execute(): void
    {
        $pendingOrders = $this->getOrders();
        if ($pendingOrders->count() == 0) {
            return;
        }

        $transactions = $this->getTransactions($pendingOrders->getFirstItem());
        if (!count($transactions)) {
            return;
        }

        foreach ($pendingOrders as $order) {
            if ($this->isTransactionDeclined($order->getIncrementId(), $transactions)) {
                $this->cancelOrder($order);
            }
        }
    }

    /**
     * Get transactions by order
     * Will be loaded all transaction in period: from: Order Created At - 1 day to ( Order Created At - 1 ) + 7 days
     *
     * @param OrderInterface $order
     *
     * @return array
     */
    private function getTransactions(OrderInterface $order): array
    {
        try {
            extract($this->getFromToRange($order->getCreatedAt()));

            return $this->getSettlementData->execute($from, $to);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return [];
    }

    /**
     * Get date ranges
     *
     * @param string $date
     *
     * @return array
     * @throws Exception
     */
    private function getFromToRange(string $date): array
    {
        $from = new \DateTime($date);

        $from->modify('-1 day');
        $to = clone $from;
        $to->modify('+' . $this->config->getSettlementDays() . ' day');

        return [
            'from' => $from->format(GetSettlementInterface::DATE_FORMAT),
            'to'   => $to->format(GetSettlementInterface::DATE_FORMAT)
        ];
    }

    /**
     * Check is transaction declined
     *
     * @param string $orderIncrementId
     * @param array $transactions
     *
     * @return bool
     */
    private function isTransactionDeclined(string $orderIncrementId, array $transactions): bool
    {
        if (!isset($transactions[$orderIncrementId])) {
            return false;
        }

        $statuses = $transactions[$orderIncrementId]['statuses'];
        return (
            count(
                array_intersect($statuses, [RequestApiInterface::STATUS_DECLINED, RequestApiInterface::STATUS_VOID])
            ) > 0
        );
    }

    /**
     * Cancel order
     *
     * @param OrderInterface $order
     *
     * @return void
     */
    private function cancelOrder(OrderInterface $order): void
    {
        try {
            $order->getPayment()->deny();
            $this->orderRepository->save($order);
        } catch (Exception $err) {
            $this->logger->error($err->getMessage());
        }
    }

    /**
     * Get orders
     *
     * @return Collection
     */
    private function getOrders(): Collection
    {
        $collection = $this->collection
            ->join(
                [
                    'payment' => 'sales_order_payment'
                ],
                'main_table.entity_id=payment.parent_id',
                [
                    'payment_method' => 'payment.method'
                ]
            );

        $collection->addFieldToFilter(
            'payment.' . OrderPaymentInterface::METHOD,
            [
                [
                    'in' => [PFConfigProvider::METHOD_CODE, ConfigProvider::METHOD_CODE, ConfigProvider::CC_VAULT_CODE]
                ]
            ]
        )->addFieldToFilter(
            OrderInterface::STATE,
            [
                [
                    'eq' => Order::STATE_PAYMENT_REVIEW
                ]
            ]
        )->setOrder(
            OrderInterface::CREATED_AT, 'ASC'
        )->setPageSize(
            self::COUNT_PROCESSED_ORDERS
        );
        return $collection;
    }
}
