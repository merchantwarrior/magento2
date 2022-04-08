<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Block\PayFrame\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider;

class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     *
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::METHOD_CODE;
    }

    /**
     * @return string
     */
    public function getNumberLast4Digits()
    {
        return $this->getTokenDetails()['maskedCC'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getTokenDetails()['type'];
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    public function getIconUrl()
    {
        return '';
    }

    public function getIconHeight()
    {
        return 0;
    }

    public function getIconWidth()
    {
        return 0;
    }
}
