<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Config;

use Magento\Framework\Config\Reader\Filesystem;

class Reader extends Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = [
        '/payment/credit_cards/type' => 'id'
    ];
}
