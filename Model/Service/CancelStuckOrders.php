<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as PFConfigProvider;
use Psr\Log\LoggerInterface;

/**
 * Class CancelStuckOrders
 */
class CancelStuckOrders
{
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
     * CancelStuckOrders constructor.
     *
     * @param Collection $collection
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Collection $collection,
        OrderRepositoryInterface $orderRepository,
        GetSettlementData $getSettlementData,
        LoggerInterface $logger
    ) {
        $this->collection = $collection;
        $this->orderRepository = $orderRepository;
        $this->getSettlementData = $getSettlementData;
        $this->logger = $logger;
    }

    /**
     * Get module version
     *
     * @return void
     */
    /**
     * @return void
     * @throws Exception
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
            if ($this->isTransactionDeclined($order, $transactions)) {
                $this->cancelOrder($order);
            }
        }
    }

    /**
     * Get transactions by order
     *
     * @param OrderInterface $order
     *
     * @return array
     */
    private function getTransactions(OrderInterface $order): array
    {
        try {
            $from = new \DateTime($order->getCreatedAt());

            $from->modify('-1 day');
            $to = clone $from;

            return $this->getSettlementData->execute(
                $from->format('Y-m-d'),
                $to->modify('+7 day')->format('Y-m-d')
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return [];
    }

    /**
     * Check is transaction declined
     *
     * @param OrderInterface $order
     * @param array $transactions
     *
     * @return bool
     */
    private function isTransactionDeclined(OrderInterface $order, array $transactions): bool
    {
        if (!isset($transactions[$order->getIncrementId()])) {
            return false;
        }

        $statuses = $transactions[$order->getIncrementId()]['statuses'];
        return (in_array(['declined', 'void'], $statuses));
    }

    /**
     * Cancel order
     *
     * @param OrderInterface $order
     *
     * @return void
     */
    private function cancelOrder(OrderInterface  $order): void
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
            OrderInterface::CREATED_AT, 'DESC'
        );
        return $collection;
    }
}
