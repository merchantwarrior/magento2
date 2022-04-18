<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Plugin;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use MerchantWarrior\Payment\Model\Service\OrderCancellation;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as PFConfigProvider;

/**
 * Cancels an order and an authorization transaction.
 */
class OrderCancelPlugin
{
    /**
     * @var OrderCancellation
     */
    private OrderCancellation $orderCancellation;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @param OrderCancellation $orderCancellation
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        OrderCancellation $orderCancellation,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->orderCancellation = $orderCancellation;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Cancels an order if an exception occurs during the order creation.
     *
     * @param CartManagementInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param null|PaymentInterface $payment
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
            $paymentCodes = [
                ConfigProvider::METHOD_CODE,
                ConfigProvider::CC_VAULT_CODE,
                PFConfigProvider::METHOD_CODE
            ];
            if (in_array($payment->getMethod(), $paymentCodes, true)) {
                $incrementId = $quote->getReservedOrderId();
                $this->orderCancellation->execute($incrementId);
            }

            throw $e;
        }
    }
}
