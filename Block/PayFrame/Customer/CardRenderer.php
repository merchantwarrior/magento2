<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Block\PayFrame\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractTokenRenderer;
use MerchantWarrior\Payment\Model\Service\GetPaymentIconsList;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider;

class CardRenderer extends AbstractTokenRenderer
{
    /**
     * @var GetPaymentIconsList
     */
    private GetPaymentIconsList $getPaymentIconsList;

    /**
     * @var array
     */
    private array $icon;

    /**
     * @param Template\Context $context
     * @param GetPaymentIconsList $getPaymentIconsList
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        GetPaymentIconsList $getPaymentIconsList,
        array $data = []
    ) {
        $this->getPaymentIconsList = $getPaymentIconsList;
        parent::__construct($context, $data);
    }

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

    /**
     * Get icon URL
     *
     * @return mixed|null
     */
    public function getIconUrl(): ?string
    {
        if ($icon = $this->getCardIcon()) {
            return $icon['url'];
        }
        return null;
    }

    /**
     * Get icon height
     *
     * @return mixed|null
     */
    public function getIconHeight(): ?int
    {
        if ($icon = $this->getCardIcon()) {
            return $icon['height'];
        }
        return null;
    }

    /**
     * Get icon width
     *
     * @return mixed|null
     */
    public function getIconWidth(): ?int
    {
        if ($icon = $this->getCardIcon()) {
            return $icon['width'];
        }
        return null;
    }

    /**
     * Get card icon
     *
     * @return array|null
     */
    protected function getCardIcon(): ?array
    {
        $type = $this->getTokenDetails()['code_alt'];

        if (isset($this->icon[$type])) {
            return $this->icon[$type];
        }

        $icons = $this->getPaymentIconsList->execute();
        if (isset($icons[$type])) {
            $this->icon[$type] = $icons[$type];
        }
        return ($this->icon[$type]) ?? null;
    }
}
