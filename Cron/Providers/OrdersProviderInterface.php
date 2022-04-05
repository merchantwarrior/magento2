<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Cron\Providers;

use Magento\Sales\Api\Data\OrderInterface;

interface OrdersProviderInterface
{
    /**
     * @return OrderInterface[]
     */
    public function provide(): array;
}
