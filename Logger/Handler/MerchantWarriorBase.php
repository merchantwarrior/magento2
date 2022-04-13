<?php

namespace MerchantWarrior\Payment\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;

class MerchantWarriorBase extends Base
{
    /**
     * Overwrite core it needs to be the exact level otherwise use different handler
     *
     * @inheritdoc
     */
    public function isHandling(array $record): bool
    {
        return $record['level'] === $this->level;
    }
}
