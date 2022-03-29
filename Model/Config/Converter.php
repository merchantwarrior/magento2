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
        }
        uasort($creditCards, [$this, '_compareCcTypes']);
        $config = [];
        foreach ($creditCards as $code => $data) {
            $config[$code] = $data;
        }
        return $config;
    }

    /**
     * Compare sort order of CC Types
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Used in callback.
     *
     * @param array $left
     * @param array $right
     * @return int
     */
    private function _compareCcTypes($left, $right)
    {
        return $left['order'] - $right['order'];
    }

    /**
     * Convert methods xml tree to array
     *
     * @param \DOMXPath $xpath
     *
     * @return array
     */
    protected function convertMethods(\DOMXPath $xpath): array
    {
        $config = [];
        /** @var \DOMNode $method */
        foreach ($xpath->query('/payment/methods/method') as $method) {
            $name = $method->attributes->getNamedItem('name')->nodeValue;
            /** @var $methodSubNode \DOMNode */
            foreach ($method->childNodes as $methodSubNode) {
                if ($methodSubNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $config[$name][$methodSubNode->nodeName] = $methodSubNode->nodeValue;
            }
        }
        return $config;
    }
}
