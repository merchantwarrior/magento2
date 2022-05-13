<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Validator\DirectAPI;

use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use MerchantWarrior\Payment\Model\Config;

/**
 * Class CountryValidator
 */
class CountryValidator extends \MerchantWarrior\Payment\Gateway\Validator\CountryValidator
{
    /**
     * @var string
     */
    protected $allowedSpecificConfigPath = Config::XML_PATH_DIRECTAPI_ALLOWED_SPECIFIC;

    /**
     * @var string
     */
    protected $allowedCountryConfigPath = Config::XML_PATH_DIRECTAPI_ALLOWED_SPECIFICCOUNTRY;
}
