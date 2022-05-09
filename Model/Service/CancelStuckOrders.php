<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
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
     * @var TimezoneInterface
     */
    private $timezone;

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
     * @param TimezoneInterface $timezone
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Collection $collection,
        TimezoneInterface $timezone,
        OrderRepositoryInterface $orderRepository,
        GetSettlementData $getSettlementData,
        LoggerInterface $logger
    ) {
        $this->collection = $collection;
        $this->timezone = $timezone;
        $this->orderRepository = $orderRepository;
        $this->getSettlementData = $getSettlementData;
        $this->logger = $logger;
    }

    /**
     * Get module version
     *
     * @return void
     */
    public function execute(): void
    {
        $data = $this->getSettlementData->execute('2022-05-01', '2022-05-09');

        foreach ($this->getOrders() as $order) {
            $diffMinutes = $this->getDiffInHours($order);
            if ($diffMinutes >= 480) {
                $this->cancelOrder($order);
            }
        }
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
        } catch (\Exception $err) {
            $this->logger->error($err->getMessage());
        }
    }

    /**
     * Get diff in hours
     *
     * @param OrderInterface $order
     *
     * @return float
     */
    private function getDiffInHours(OrderInterface $order): float
    {
        $timeZone = new \DateTimeZone($this->timezone->getConfigTimezone('store', $order->getStore()));

        $now = $this->timezone->date()->setTimezone($timeZone)->getTimestamp();
        $orderCreatedAt = $this->timezone->date($order->getCreatedAt())->setTimezone($timeZone)->getTimestamp();

        return round (($now - $orderCreatedAt) / 60);
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
            'payment.method',
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
        );
        return $collection;
    }
}
