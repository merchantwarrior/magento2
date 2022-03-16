<?php

namespace MerchantWarrior\Payment\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use MerchantWarrior\Payment\Helper\Data;

class Version extends Field
{
    /**
     * @var Data
     */
    protected $mwHelper;

    /**
     * Version constructor.
     *
     * @param Data $mwHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $mwHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mwHelper = $mwHelper;
    }

    /**
     * Retrieve the setup version of the extension
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->mwHelper->getModuleVersion();
    }
}
