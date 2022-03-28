<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Block\Adminhtml\Payment;

use Magento\Framework\Exception\InputException;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data;
use Magento\Backend\Block\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use MerchantWarrior\Payment\Model\Ui\ConfigProvider as MWConfigProvider;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider as MWPayFrameConfigProvider;

class Info extends Template
{
    /**
     * @var Data
     */
    public Data $paymentData;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var null|OrderPaymentInterface
     */
    protected ?OrderPaymentInterface $paymentMethod = null;

    /**
     * @param Context $context
     * @param Data $paymentData
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $paymentData,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->paymentData = $paymentData;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function shouldDisplayMWSection(): bool
    {
        return $this->isMWPaymentMethod();
    }

    /**
     * Check is current method is Merchant Warrior
     *
     * @return bool
     */
    public function isMWPaymentMethod(): bool
    {
        if ($this->getMethod() !== MWPayFrameConfigProvider::METHOD_CODE) {
            return false;
        }
        return true;
    }

    /**
     * Get payment method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->getPayment()->getMethod();
    }

    /**
     * Get payment card number
     *
     * @return string|null
     */
    public function getPaymentCardNumber(): ?string
    {
        return $this->getAdditionalInformation('paymentCardNumber');
    }

    /**
     * Get transaction ID
     *
     * @return string|null
     */
    public function getTransactionID(): ?string
    {
        return $this->getAdditionalInformation('transactionID');
    }

    /**
     * Get additional info
     *
     * @param string $key
     *
     * @return string|null
     */
    private function getAdditionalInformation(string $key): ?string
    {
        $additionalInformation = $this->getPayment()->getAdditionalInformation();
        if (count($additionalInformation) && isset($additionalInformation[$key])) {
            return $additionalInformation[$key];
        }
        return null;
    }

    /**
     * Get payment method
     *
     * @return OrderPaymentInterface|null
     */
    private function getPayment(): ?OrderPaymentInterface
    {
        if (!$this->paymentMethod) {
            $this->paymentMethod = $this->getOrder()->getPayment();
        }
        return $this->paymentMethod;
    }

    /**
     * Get order
     *
     * @return OrderInterface|null
     */
    private function getOrder(): ?OrderInterface
    {
        $id = $this->getRequest()->getParam('order_id');
        try {
            return $this->orderRepository->get($id);
        } catch (NoSuchEntityException|InputException $e) {
            return null;
        }
    }
}
