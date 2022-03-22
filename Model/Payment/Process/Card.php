<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Payment\Process;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\GuestBillingAddressManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\CartInterface;
use MerchantWarrior\Payment\Api\Payment\Process\CardInterface;
use MerchantWarrior\Payment\Api\Payframe\ProcessCardInterface;

class Card implements CardInterface
{
    /**
     * @var GuestBillingAddressManagementInterface
     */
    private GuestBillingAddressManagementInterface $billingAddressManagement;

    /**
     * @var GuestCartManagementInterface
     */
    private GuestCartManagementInterface $cartManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var ProcessCardInterface
     */
    private ProcessCardInterface $processCard;

    /**
     * @param GuestBillingAddressManagementInterface $billingAddressManagement
     * @param GuestCartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ProcessCardInterface $processCard
     */
    public function __construct(
        GuestBillingAddressManagementInterface $billingAddressManagement,
        GuestCartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        ProcessCardInterface $processCard
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->processCard = $processCard;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        string $payframeToken,
        string $payframeKey,
        string $cartId,
        string $email,
        AddressInterface $billingAddress = null
    ): string {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

        if ($billingAddress) {
            $billingAddress->setEmail($email);
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
        } else {
            $quote->getBillingAddress()->setEmail($email);
        }

        $transactionData = [
            'payframeToken'     => $payframeToken,
            'payframeKey'       => $payframeKey
        ];
        $transactionData = $this->formData($transactionData, $quote, $billingAddress);

        $result = $this->processCard->execute($transactionData);

        return '';
    }

    /**
     * Form transaction data
     *
     * @param array $transactionData
     * @param CartInterface $quote
     * @param AddressInterface $billingAddress
     * @return array
     */
    private function formData(array $transactionData, CartInterface $quote, AddressInterface $billingAddress)
    {
        $items = [];
        foreach ($quote->getItems() as $item) {
            $items[] = $item->getSku();
        }
        $product = implode(',', $items);

        $data = [
            'transactionAmount'     => $quote->getGrandTotal(),
            'transactionCurrency'   => $quote->getGlobalCurrencyCode(),
            'transactionProduct'    => $product,
            'customerName'      => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
            'customerCountry'   => $billingAddress->getCountryId(),
            'customerState'     => $billingAddress->getRegion(),
            'customerCity'      => $billingAddress->getCity(),
            'customerAddress'   => $billingAddress->getStreet(),
            'customerPostCode'  => $billingAddress->getPostcode(),
            'customerPhone'     => $billingAddress->getTelephone(),
            'customerEmail'     => $billingAddress->getEmail(),
            'customerIP'        => $quote->getRemoteIp(),
        ];

        return array_merge($transactionData, $data);
    }
}
