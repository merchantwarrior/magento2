<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as PFConfigProvider;

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
     * @var OrderManagementInterface
     */
    private OrderManagementInterface $orderManagement;

    /**
     * CancelStuckOrders constructor.
     *
     * @param Collection $collection
     * @param TimezoneInterface $timezone
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Collection $collection,
        TimezoneInterface $timezone,
        OrderManagementInterface $orderManagement
    ) {
        $this->collection = $collection;
        $this->timezone = $timezone;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Get module version
     *
     * @return void
     */
    public function execute(): void
    {
        $now = $this->timezone->date();
        foreach ($this->getOrders() as $order) {
            $purchasedDate = $this->timezone->date($order->getCreatedAt())->add(new \DateInterval('P1D'));
            if ($now > $purchasedDate) {
                $order->getPayment()->deny();

                $this->orderManagement->cancel($order->getEntityId());
            }
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
