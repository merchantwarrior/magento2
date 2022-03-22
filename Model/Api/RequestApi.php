<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\HashGenerator;

abstract class RequestApi implements RequestApiInterface
{
    /**#@+
     * Request Mode
     */
    const REQUEST_MODE_JSON = true;
    /**#@-*/

    /**#@+
     * Success response code
     */
    const SUCCESS_CODE = 0;
    /**#@-*/

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $eventManager;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var HashGenerator
     */
    protected HashGenerator $hashGenerator;

    /**
     * @var Parser
     */
    protected Parser $xmlParser;

    /**
     * @var bool
     */
    protected bool $authoriseCall = false;

    /**
     * @var int|null
     */
    protected ?int $callTime = null;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var array
     */
    protected array $response = [];

    /**
     * @var int
     */
    protected int $status = 200;

    /**
     * Abstract API constructor.
     *
     * @param Config $config
     * @param HashGenerator $hashGenerator
     * @param ClientInterface $client
     * @param ManagerInterface $manager
     * @param TimezoneInterface $timezone
     * @param SerializerInterface $serializer
     * @param Parser $xmlParser
     */
    public function __construct(
        Config $config,
        HashGenerator $hashGenerator,
        ClientInterface $client,
        ManagerInterface $manager,
        TimezoneInterface $timezone,
        SerializerInterface $serializer,
        Parser $xmlParser
    ) {
        $this->config = $config;
        $this->hashGenerator = $hashGenerator;
        $this->client = $client;
        $this->eventManager = $manager;
        $this->timezone = $timezone;
        $this->serializer = $serializer;
        $this->xmlParser = $xmlParser;
    }

    /**
     * Get base API url
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->config->getApiUrl();
    }

    /**
     * Send post request data
     *
     * @param string $key
     * @param string $url
     * @param array $data
     *
     * @return void
     */
    protected function sendPostRequest(string $key, string $url, array $data): void
    {
        try {
            $this->beforeCall($data);

            if (self::REQUEST_MODE_JSON) {
                $this->client->addHeader('MW-API-VERSION', '2.0');
                $this->client->addHeader('content-type', 'application/json');
            }
            $this->client->setOption((string)CURLOPT_CUSTOMREQUEST, 'POST');

            if (self::REQUEST_MODE_JSON) {
                $this->client->post($this->getApiUrl() . $url, $this->serializer->serialize($data));
            } else {
                $this->client->post($this->getApiUrl() . $url, $data);
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
        if (!empty($result)) {
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
            $this->response[$key] = (!is_array($result)) ? new DataObject([$result]) : new DataObject($result);
        }
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
     * Do actions before send request
     *
     * @param array $data
     *
     * @return void
     */
    protected function beforeCall(array $data = []): void
    {
        $this->eventManager->dispatch(
            'merchant_warrior_post_before',
            [
                'method'      => __METHOD__,
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
                'method'        => __METHOD__,
                'status'        => $this->status,
                'response_data' => $this->getResponse($key)->toArray(),
                'call_time'     => $this->getCallTime(),
            ]
        );
    }
}
