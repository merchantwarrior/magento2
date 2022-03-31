<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use MerchantWarrior\Payment\Model\Service\GetModuleVersion;

class Version extends Field
{
    /**
     * @var GetModuleVersion
     */
    protected GetModuleVersion $getModuleVersion;

    /**
     * Version constructor.
     *
     * @param GetModuleVersion $getModuleVersion
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        GetModuleVersion $getModuleVersion,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getModuleVersion = $getModuleVersion;
    }

    /**
     * Retrieve the setup version of the extension
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->getModuleVersion->execute();
    }
}
