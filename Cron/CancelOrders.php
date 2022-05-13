<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Cron;

use MerchantWarrior\Payment\Model\Service\CancelStuckOrders;

class CancelOrders
{
    /**
     * @var CancelStuckOrders
     */
    private $cancelStuckOrders;

    /**
     * @param CancelStuckOrders $cancelStuckOrders
     */
    public function __construct(
        CancelStuckOrders $cancelStuckOrders
    ) {
        $this->cancelStuckOrders = $cancelStuckOrders;
    }

    /**
     * Cancel stuck orders
     *
     * @return void
     */
    public function execute(): void
    {
        $this->cancelStuckOrders->execute();
    }
}
