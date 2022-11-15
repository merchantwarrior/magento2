<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper;

/**
 * Class AbstractDataBuilder
 */
abstract class AbstractDataBuilder implements BuilderInterface
{
    /**
     * Reads payment from subject
     *
     * @param array $subject
     *
     * @return PaymentDataObjectInterface
     */
    protected function readPayment(array $subject): PaymentDataObjectInterface
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Get formatted amount
     *
     * @param float $price
     *
     * @return string
     */
    protected function getTransactionAmount(float $price): string
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * Clear transaction ID
     *
     * @param string $transactionId
     *
     * @return string
     */
    protected function clearTransactionId(string $transactionId): string
    {
        if (strpos($transactionId, '-refund') !== false) {
            $transactionId = str_replace('-refund', '', $transactionId);
        }
        if (strpos($transactionId, '-capture') !== false) {
            $transactionId = str_replace('-capture', '', $transactionId);
        }
        return $transactionId;
    }
}
