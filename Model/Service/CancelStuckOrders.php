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
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

/**
 * Class CancelStuckOrders
 */
class CancelStuckOrders
{
    /**
     * @var Collection
     */
    private Collection $collection;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @var MerchantWarriorLogger
     */
    private MerchantWarriorLogger $warriorLogger;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * CancelStuckOrders constructor.
     *
     * @param Collection $collection
     * @param TimezoneInterface $timezone
     * @param OrderRepositoryInterface $orderRepository
     * @param MerchantWarriorLogger $warriorLogger
     */
    public function __construct(
        Collection $collection,
        TimezoneInterface $timezone,
        OrderRepositoryInterface $orderRepository,
        MerchantWarriorLogger $warriorLogger
    ) {
        $this->collection = $collection;
        $this->timezone = $timezone;
        $this->orderRepository = $orderRepository;
        $this->warriorLogger = $warriorLogger;
    }

    /**
     * Get module version
     *
     * @return void
     * @throws \Exception
     */
    public function execute(): void
    {
        foreach ($this->getOrders() as $order) {
            if ((int)$order->getId() !== 94) {
                continue;
            }

            $diffMinutes = $this->getDiffInHours($order);
            if ($diffMinutes >= 480) {
                try {
                    $order->getPayment()->deny();
                    $this->orderRepository->save($order);
                } catch (\Exception $err) {
                    $this->warriorLogger->error($err->getMessage());
                }
            }
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
