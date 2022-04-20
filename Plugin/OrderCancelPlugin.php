<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Plugin;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use MerchantWarrior\Payment\Model\Service\RollbackTransaction;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;

/**
 * Cancels an order and an authorization transaction.
 */
class OrderCancelPlugin
{
    /**
     * @var RollbackTransaction
     */
    private RollbackTransaction $rollbackTransaction;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @param RollbackTransaction $rollbackTransaction
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        RollbackTransaction $rollbackTransaction,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->rollbackTransaction = $rollbackTransaction;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Cancels an order if an exception occurs during the order creation.
     *
     * @param CartManagementInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param null|PaymentInterface $payment
     *
     * @return int
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        PaymentInterface $payment = null
    ) {
        try {
            return $proceed($cartId, $payment);
        } catch (\Exception $e) {
            $quote = $this->quoteRepository->get((int) $cartId);
            $payment = $quote->getPayment();

            if (0 === strpos($payment->getMethod(), ConfigProvider::METHOD_CODE)) {
                $incrementId = $quote->getReservedOrderId();
                $this->rollbackTransaction->execute($incrementId);
            }
            throw $e;
        }
    }
}
