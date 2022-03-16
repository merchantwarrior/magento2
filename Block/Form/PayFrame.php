<?php

namespace MerchantWarrior\Payment\Block\Form;

use Magento\OfflinePayments\Block\Form\AbstractInstruction;

class PayFrame extends AbstractInstruction
{
    /**
     * @var string
     */
    protected $_template = 'MerchantWarrior_Payment::form/payframe.phtml';
}
