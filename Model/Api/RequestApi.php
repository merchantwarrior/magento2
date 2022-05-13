<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MerchantWarrior\Payment\Api\Direct\GetSettlementInterface;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\HashGenerator;
use MerchantWarrior\Payment\Model\Service\SaveToZipData;

abstract class RequestApi implements RequestApiInterface
{
    /**#@+
     * Request Mode
     */
    public const REQUEST_MODE_JSON = true;
    /**#@-*/

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var HashGenerator
     */
    protected $hashGenerator;

    /**
     * @var Parser
     */
    protected $xmlParser;

    /**
     * @var SaveToZipData
     */
    protected $saveToZipData;

    /**
     * @var int|null
     */
    protected $callTime = null;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $response = [];

    /**
     * @var int
     */
    protected $status = 200;

    /**
     * Abstract API constructor.
     *
     * @param Config $config
     * @param HashGenerator $hashGenerator
     * @param ClientInterface $client
     * @param ManagerInterface $manager
     * @param TimezoneInterface $timezone
     * @param SerializerInterface $serializer
     * @param SaveToZipData $saveToZipData
     * @param Parser $xmlParser
     */
    public function __construct(
        Config $config,
        HashGenerator $hashGenerator,
        ClientInterface $client,
        ManagerInterface $manager,
        TimezoneInterface $timezone,
        SerializerInterface $serializer,
        SaveToZipData $saveToZipData,
        Parser $xmlParser
    ) {
        $this->config = $config;
        $this->hashGenerator = $hashGenerator;
        $this->client = $client;
        $this->eventManager = $manager;
        $this->timezone = $timezone;
        $this->serializer = $serializer;
        $this->saveToZipData = $saveToZipData;
        $this->xmlParser = $xmlParser;
    }

    /**
     * Get error data
     *
     * @param string $method
     *
     * @return array
     */
    public function getError(string $method): array
    {
        return [
            'responseCode' => $this->getResponseCode($method),
            'responseMessage' => $this->getResponseMessage($method),
            'responseAuthCode' => $this->getAuthResponseCode($method),
            'error' => __($this->getAuthResponseMessage($method))->render()
        ];
    }

    /**
     * Set request
     *
     * @param string $method
     * @param array $data
     *
     * @return array
     * @throws LocalizedException
     */
    protected function sendRequest(string $method, array $data): array
    {
        $data = $this->formData($data);

        $this->sendPostRequest($method, $data);

        if ($this->getResponseCode($method) !== '0') {
            throw new LocalizedException(
                __($this->getResponseMessage($method)),
                null,
                $this->getResponseCode($method)
            );
        }
        return $this->getResponse($method)->toArray();
    }

    /**
     * Send post request data
     *
     * @param string $key
     * @param array $data
     *
     * @return void
     */
    protected function sendPostRequest(string $key, array $data): void
    {
        try {
            $this->beforeCall($data);

            if (self::REQUEST_MODE_JSON) {
                $this->client->addHeader('MW-API-VERSION', '2.0');
                $this->client->addHeader('content-type', 'application/json');
            }
            $this->client->setOption((string)CURLOPT_CUSTOMREQUEST, 'POST');

            if (self::REQUEST_MODE_JSON) {
                $this->client->post($this->getApiUrl(), $this->serializer->serialize($data));
            } else {
                $this->client->post($this->getApiUrl(), $data);
            }

            $this->afterCall($key);
        } catch (\Exception $e) {
            $this->eventManager->dispatch(
                'merchant_warrior_post_after_error',
                [
                    'method'    => __METHOD__,
                    'data'      => $data,
                    'call_time' => $this->getCallTime(),
                    'error'     => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Get call time
     *
     * @return int|null
     */
    protected function getCallTime(): ?int
    {
        if (!$this->callTime) {
            $this->callTime = $this->timezone->date()->getTimestamp();
        }
        return $this->callTime;
    }

    /**
     * From request array
     *
     * @param array $params
     *
     * @return array
     */
    protected function formData(array $params = []): array
    {
        $data = [
            'merchantUUID' => $this->config->getMerchantUserId(),
            'apiKey' => $this->config->getApiKey(),
            'hash' => $this->getHash($params)
        ];
        foreach ($params as $key => $param) {
            $data[$key] = $param;
        }
        return $data;
    }

    /**
     * Get status
     *
     * @return int
     */
    protected function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get transaction hash
     *
     * @param array $data
     *
     * @return string
     */
    protected function getHash(array $data): string
    {
        return $this->hashGenerator->execute($data);
    }

    /**
     * Get response data
     *
     * @param string $key
     *
     * @return null|DataObject
     */
    protected function getResponse(string $key): ?DataObject
    {
        if (isset($this->response[$key])) {
            return $this->response[$key];
        }
        $this->response[$key] = new DataObject([]);

        $result = $this->client->getBody();
        if (empty($result)) {
            return $this->response[$key];
        }

        if ($key === GetSettlementInterface::API_METHOD) {
            $result = [
                'method' => GetSettlementInterface::API_METHOD,
                'mwResponse' => $result,
                'responseCode' => '0'
            ];
        } else {
            if (self::REQUEST_MODE_JSON) {
                $result = $this->serializer->unserialize($result);
            } else {
                try {
                    $result = $this->xmlParser->loadXML($result)->xmlToArray();
                    $result = $result['mwResponse'];
                } catch (\Exception $e) {
                    $result = [];
                }
            }
        }
        $this->response[$key] = (!is_array($result)) ? new DataObject([$result]) : new DataObject($result);

        return $this->response[$key];
    }

    /**
     * Get error message
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getResponseMessage(string $key): ?string
    {
        if ($this->getResponse($key)->isEmpty()) {
            return null;
        }
        return $this->getResponse($key)->getData('responseMessage');
    }

    /**
     * Get response message
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getResponseCode(string $key): ?string
    {
        if ($this->getResponse($key)->isEmpty()) {
            return null;
        }
        return $this->getResponse($key)->getData('responseCode');
    }

    /**
     * Get auth error message
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getAuthResponseMessage(string $key): ?string
    {
        if ($this->getResponse($key)->isEmpty()) {
            return null;
        }
        return $this->getResponse($key)->getData('authMessage');
    }

    /**
     * Get auth response message
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getAuthResponseCode(string $key): ?string
    {
        if ($this->getResponse($key)->isEmpty()) {
            return null;
        }
        return $this->getResponse($key)->getData('authResponseCode');
    }

    /**
     * Do action before send request
     *
     * @param array $data
     *
     * @return void
     */
    protected function beforeCall(array $data = []): void
    {
        $this->clearData();

        $this->eventManager->dispatch(
            'merchant_warrior_post_before',
            [
                'api_url'     => $this->getApiUrl(),
                'passed_data' => $data,
                'call_time'   => $this->getCallTime(),
            ]
        );
    }

    /**
     * Call additional functions after api call
     *
     * @param string $key
     *
     * @return void
     */
    protected function afterCall(string $key): void
    {
        $this->status = $this->client->getStatus();

        $this->eventManager->dispatch(
            'merchant_warrior_post_after',
            [
                'status'        => $this->status,
                'response_data' => $this->getResponse($key)->toArray(),
                'call_time'     => $this->getCallTime(),
            ]
        );
    }

    /**
     * Clear stored data
     *
     * @return void
     */
    private function clearData(): void
    {
        $this->data = [];
        $this->response = [];
        $this->status = 200;
    }
}
