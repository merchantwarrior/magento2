<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Config;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * @param \DOMDocument $source
     *
     * @return array
     */
    public function convert($source): array
    {
        $xpath = new \DOMXPath($source);
        return [
            'credit_cards' => $this->convertCreditCards($xpath),
        ];
    }

    /**
     * Convert credit cards xml tree to array
     *
     * @param \DOMXPath $xpath
     *
     * @return array
     */
    protected function convertCreditCards(\DOMXPath $xpath): array
    {
        $creditCards = [];
        /** @var \DOMNode $type */
        foreach ($xpath->query('/payment/credit_cards/type') as $type) {
            $creditCards = $this->formCreditCards($type, $creditCards);
        }

        uasort($creditCards, static fn($left, $right) => $left['order'] - $right['order']);
        $config = [];
        foreach ($creditCards as $code => $data) {
            $config[$code] = $data;
        }
        return $config;
    }

    /**
     * Form credit cards array
     *
     * @param \DOMNode $type
     * @param array $creditCards
     *
     * @return array
     */
    private function formCreditCards(\DOMNode $type, array $creditCards): array
    {
        $typeArray = [];

        /** @var $typeSubNode \DOMNode */
        foreach ($type->childNodes as $typeSubNode) {
            switch ($typeSubNode->nodeName) {
                case 'label':
                    $typeArray['name'] = $typeSubNode->nodeValue;
                    break;
                case 'code_alt':
                    $typeArray['code_alt'] = $typeSubNode->nodeValue;
                    break;
                default:
                    break;
            }
        }

        $typeAttributes = $type->attributes;
        $typeArray['order'] = $typeAttributes->getNamedItem('order')->nodeValue;
        $ccId = $typeAttributes->getNamedItem('id')->nodeValue;
        $creditCards[$ccId] = $typeArray;

        return $creditCards;
    }
}
