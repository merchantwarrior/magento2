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
    protected string $allowedSpecificConfigPath = Config::XML_PATH_DIRECTAPI_ALLOWED_SPECIFIC;
    protected string $allowedCountryConfigPath = Config::XML_PATH_DIRECTAPI_ALLOWED_SPECIFICCOUNTRY;
}
