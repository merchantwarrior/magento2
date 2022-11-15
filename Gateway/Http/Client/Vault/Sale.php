<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Http\Client\Vault;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use MerchantWarrior\Payment\Api\Token\ProcessInterface;

class Sale implements ClientInterface
{
    /**
     * @var ProcessInterface
     */
    private $process;

    /**
     * @param ProcessInterface $process
     */
    public function __construct(
        ProcessInterface $process
    ) {
        $this->process = $process;
    }

    /**
     * Place request
     *
     * @param TransferInterface $transferObject
     *
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $transactionData = $transferObject->getBody();

        if (count($transactionData)) {
            try {
                $result = $this->process->execute(ProcessInterface::API_METHOD_PROCESS_CARD, $transactionData);
            } catch (LocalizedException $err) {
                $result = $this->process->getError(ProcessInterface::API_METHOD_PROCESS_CARD);
            }
            return $result;
        }
        return [];
    }
}
