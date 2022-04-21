<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Console;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use MerchantWarrior\Payment\Model\Service\CancelStuckOrders;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ValidateOrdersCommand
 */
class CancelOrdersCommand extends Command
{
    /**
     * @var State
     */
    private State $appState;

    /**
     * @var CancelStuckOrders
     */
    private CancelStuckOrders $cancelStuckOrders;

    /**
     * ProcessOrdersCommand constructor.
     *
     * @param State $state
     * @param CancelStuckOrders $cancelStuckOrders
     */
    public function __construct(
        State $state,
        CancelStuckOrders $cancelStuckOrders
    ) {
        parent::__construct();

        $this->appState = $state;
        $this->cancelStuckOrders = $cancelStuckOrders;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('merchant-warrior:orders:cancel')
            ->setDescription('Cancel Stuck Orders');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            unset($e);
        }

        $this->cancelStuckOrders->execute();

        $output->writeln("<info>Orders been processed!</info>");

        return 0;
    }
}
