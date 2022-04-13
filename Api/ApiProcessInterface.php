<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Api;

/**
 * Interface ApiProcessInterface
 */
interface ApiProcessInterface
{
    /**
     * Get error data
     * returned format:
     * [
     *  'responseCode' => '',
     *  'responseAuthCode' => '',
     *  'error' => ''
     * ];
     *
     * @param string $method
     *
     * @return array
     */
    public function getError(string $method): array;
}
