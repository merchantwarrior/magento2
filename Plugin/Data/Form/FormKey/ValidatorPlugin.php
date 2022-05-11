<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Plugin\Data\Form\FormKey;

use Closure;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;

class ValidatorPlugin
{
    public function aroundValidate(Validator $subject, Closure $proceed, RequestInterface $request)
    {
        if('mwarrior' == $request->getModuleName() && 'get' == $request->getActionName()) {
            return true;
        }
        return $proceed($request);
    }
}
