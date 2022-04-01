<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Block\Info;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info;
use MerchantWarrior\Payment\Model\Config;

class PayFrame extends Info
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;

    /**
     * @param Context $context
     * @param DataObjectFactory $dataObjectFactory
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->config = $config;
    }

    /**
     * Get specific information for the invoice pdf.
     *
     * @return array
     */
    public function getSpecificInformation(): array
    {
        return $this->getDisplayedInformation();
    }

    /**
     * Getting all displayed information
     *
     * @return array
     */
    private function getDisplayedInformation(): array
    {
        $data = parent::getSpecificInformation();
        try {
            $transport = $this->dataObjectFactory->create(['data' => $data]);
            $info = $this->getInfo();

            if ($cardType = $info->getAdditionalInformation('cardType')) {
                $transport->setData((string)__('Card Type'), $this->formCardType($cardType));
            }

            if ($cardNumber = $info->getAdditionalInformation('paymentCardNumber')) {
                $transport->setData((string)__('Card Number'), $this->formCardNumber($cardNumber));
            }

            if ($transactionId = $info->getAdditionalInformation('transactionID')) {
                $transport->setData((string)__('Transaction ID'), $transactionId);
            }
            return $transport->getData();
        } catch (LocalizedException $e) {
            return [];
        }
    }

    /**
     * Form Card Type
     *
     * @param string $cardType
     *
     * @return string
     */
    private function formCardType(string $cardType): string
    {
        $cardsTypes = $this->config->getCcTypes();

        $result = $cardType;
        array_walk($cardsTypes, static function(&$card) use (&$result, $cardType) {
            if ($card['code_alt'] === $cardType) {
                $result = $card['name'];
            }
        });
        return $result;
    }

    /**
     * Form card number
     *
     * @param string $cardNumber
     *
     * @return string
     */
    private function formCardNumber(string $cardNumber): string
    {
        return '****' . substr($cardNumber, -4);
    }
}
