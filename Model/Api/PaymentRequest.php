<?php

namespace MerchantWarrior\Payment\Model\Api;

use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\Context;
use MerchantWarrior\Payment\Helper\Data;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;

class PaymentRequest extends DataObject
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var Data
     */
    private $mwHelper;

    /**
     * @var MerchantWarriorLogger
     */
    private $mwLogger;

    /**
     * PaymentRequest constructor.
     *
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param Data $mwHelper
     * @param MerchantWarriorLogger $mwLogger
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        Data $mwHelper,
        MerchantWarriorLogger $mwLogger,
        array $data = []
    ) {
        $this->appState = $context->getAppState();
        $this->mwHelper = $mwHelper;
        $this->mwLogger = $mwLogger;
    }

    // TODO
}
