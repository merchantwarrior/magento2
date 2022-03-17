<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MerchantWarrior\Payment\Model\Debugger;

/**
 * Class DebugObserver
 */
class DebugObserver implements ObserverInterface
{
    /**
     * @var Debugger
     */
    private Debugger $debugger;

    /**
     * DebugObserver constructor.
     *
     * @param Debugger $debugger
     */
    public function __construct(
        Debugger $debugger
    ) {
        $this->debugger = $debugger;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $this->debugger->setData($observer->getData())->execute();
    }
}
