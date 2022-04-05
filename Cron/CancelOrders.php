<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Cron;

use Magento\Sales\Api\OrderManagementInterface;

class CancelOrders
{
    /**
     * @var OrdersProviderInterface[]
     */
    private $providers;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @param OrderManagementInterface $orderManagement
     * @param array $providers
     */
    public function __construct(
        OrderManagementInterface $orderManagement,
        array $providers
    ) {
        $this->providers = $providers;
        $this->orderManagement = $orderManagement;
    }

    public function execute()
    {
        foreach ($this->providers as $provider) {
            $this->cancelOrders($provider);
        }
    }

    private function cancelOrders($provider)
    {
        foreach ($provider->provide() as $order) {
            $this->orderManagement->cancel($order->getEntityId());
        }
    }
}
