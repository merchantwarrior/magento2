<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Address data builder
 */
class AddressDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
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
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        $productMetadata = \Magento\Framework\App\ObjectManager::getInstance()->get(ProductMetadataInterface::class);
        $version = $productMetadata->getVersion();

        if (version_compare($version, '2.4.4', '>=')) {
            $customerAddress = $billingAddress->getStreetLine1().$billingAddress->getStreetLine2();
        } else {
            $customerAddress = implode(', ', $billingAddress->getStreet());
        }
        
        return [
            RequestApiInterface::CUSTOMER_COUNTRY   => $billingAddress->getCountryId(),
            RequestApiInterface::CUSTOMER_STATE     => $billingAddress->getRegionCode(),
            RequestApiInterface::CUSTOMER_CITY      => $billingAddress->getCity(),
            RequestApiInterface::CUSTOMER_ADDRESS   => $customerAddress,
            RequestApiInterface::CUSTOMER_POST_CODE => $billingAddress->getPostcode()
        ];
    }
}
