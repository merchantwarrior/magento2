<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Payment\Process;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\GuestBillingAddressManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\CartInterface;
use MerchantWarrior\Payment\Api\Payment\Process\CardInterface;
use MerchantWarrior\Payment\Api\Payframe\ProcessCardInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;

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
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param GuestBillingAddressManagementInterface $billingAddressManagement
     * @param GuestCartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ProcessCardInterface $processCard
     * @param SerializerInterface $serializer
     */
    public function __construct(
        GuestBillingAddressManagementInterface $billingAddressManagement,
        GuestCartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        ProcessCardInterface $processCard,
        SerializerInterface $serializer
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->processCard = $processCard;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        string $payframeToken,
        string $payframeKey,
        string $tdsToken,
        string $cartId,
        string $email,
        AddressInterface $billingAddress = null
    ): string {
        $result = [
            'status' => 0,
            'data' => [
                "cardExpiryYear" => "21",
                "responseMessage" => "Transaction approved",
                "transactionReferenceID" => "12345",
                "cardType" => "mc",
                "responseCode" => "0",
                "authCode" => "731357421",
                "transactionAmount" => "1.00",
                "authResponseCode" => "08",
                "transactionID" => "1336-20be3569-b600-11e6-b9c3-005056b209e0",
                "receiptNo" => "731357421",
                "cardExpiryMonth" => "05",
                "customHash" => "65b172551b7d3a0706c0ce5330c98470",
                "authSettledDate" => "2016-11-29",
                "paymentCardNumber" => "512345XXXXXX2346",
                "authMessage" => "Honour with identification"
            ]
        ];

        return $this->serializer->serialize($result);

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

        try {
            $transactionData = [
                RequestApiInterface::PAYFRAME_TOKEN => $payframeToken,
                RequestApiInterface::PAYFRAME_KEY => $payframeKey
            ];

            if (!empty($tdsToken)) {
                $transactionData[RequestApiInterface::PAYFRAME_THREE_DS_TOKEN] = $tdsToken;
            }

            $transactionData = $this->formTransactionData($transactionData, $quote);
            $transactionData = $this->formCustomerData($transactionData, $quote, $billingAddress);

            $result = $this->processCard->execute($transactionData);

            $result = [
                'status' => 0,
                'message' => $result->getData()
            ];
        } catch (\Exception $e) {
            $result = [
                'status' => 0,
                'message' => $e->getMessage()
            ];
        }

        $result = [
            'status' => 0,
            'data' => [
                "custom1" => [],
                "cardExpiryYear" => "21",
                "custom2" => [],
                "custom3" => [],
                "responseMessage" => "Transaction approved",
                "transactionReferenceID" => "12345",
                "cardType" => "mc",
                "responseCode" => "0",
                "authCode" => "731357421",
                "transactionAmount" => "1.00",
                "authResponseCode" => "08",
                "transactionID" => "1336-20be3569-b600-11e6-b9c3-005056b209e0",
                "receiptNo" => "731357421",
                "cardExpiryMonth" => "05",
                "customHash" => "65b172551b7d3a0706c0ce5330c98470",
                "authSettledDate" => "2016-11-29",
                "paymentCardNumber" => "512345XXXXXX2346",
                "authMessage" => "Honour with identification"
            ]
        ];
        return $this->serializer->serialize($result);
    }

    /**
     * Form transaction data
     *
     * @param array $transactionData
     * @param CartInterface $quote
     * @param AddressInterface $billingAddress
     *
     * @return array
     */
    private function formCustomerData(
        array $transactionData,
        CartInterface $quote,
        AddressInterface $billingAddress
    ): array {
        $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        $customerAddress = implode(', ', $billingAddress->getStreet());
        $data = [
            RequestApiInterface::CUSTOMER_NAME      => $customerName,
            RequestApiInterface::CUSTOMER_COUNTRY   => $billingAddress->getCountryId(),
            RequestApiInterface::CUSTOMER_STATE     => $billingAddress->getRegion(),
            RequestApiInterface::CUSTOMER_CITY      => $billingAddress->getCity(),
            RequestApiInterface::CUSTOMER_ADDRESS   => $customerAddress,
            RequestApiInterface::CUSTOMER_POST_CODE => $billingAddress->getPostcode(),
            RequestApiInterface::CUSTOMER_PHONE     => $billingAddress->getTelephone(),
            RequestApiInterface::CUSTOMER_EMAIL     => $billingAddress->getEmail(),
            RequestApiInterface::CUSTOMER_IP        => $quote->getRemoteIp(),
        ];
        return array_merge($transactionData, $data);
    }

    /**
     * Form transaction data
     *
     * @param array $transactionData
     * @param CartInterface $quote
     *
     * @return array
     */
    private function formTransactionData(array $transactionData, CartInterface $quote): array
    {
        $items = [];
        foreach ($quote->getItems() as $item) {
            $items[] = $item->getSku();
        }
        $product = implode(',', $items);

        $transactionData[RequestApiInterface::TRANSACTION_AMOUNT]   = $quote->getGrandTotal();
        $transactionData[RequestApiInterface::TRANSACTION_CURRENCY] = $quote->getGlobalCurrencyCode();
        $transactionData[RequestApiInterface::TRANSACTION_PRODUCT]  = $product;

        return $transactionData;
    }
}
