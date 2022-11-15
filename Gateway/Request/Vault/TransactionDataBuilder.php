<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request\Vault;

use Magento\Vault\Api\PaymentTokenManagementInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;
use MerchantWarrior\Payment\Gateway\Request\AbstractDataBuilder;

/**
 * VaultTransactionDataBuilder data builder
 */
class TransactionDataBuilder extends AbstractDataBuilder
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     */
    public function __construct(
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Add delivery\billing details into request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();

        $data = $this->paymentTokenManagement->getByPublicHash(
            $payment->getAdditionalInformation('public_hash'),
            $payment->getAdditionalInformation('customer_id'),
        );

        $result = [
            'cardID' => $data->getGatewayToken()
        ];

        if ($tdsToken = $payment->getAdditionalInformation(RequestApiInterface::PAYFRAME_TDS_TOKEN)) {
            $result[RequestApiInterface::PAYFRAME_THREE_DS_TOKEN] = $tdsToken;
        }

        if ($cvvCode = $payment->getAdditionalInformation(RequestApiInterface::PAYMENT_CARD_CSC)) {
            $result[RequestApiInterface::PAYMENT_CARD_CSC] = $cvvCode;
        }
        return $result;
    }
}
