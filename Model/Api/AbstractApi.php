<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Model\Api;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\HashGenerator;

abstract class AbstractApi
{
    /**#@+
     * Success response code
     */
    const SUCCESS_CODE = 0;
    /**#@-*/

    /**#@+
     * Authorization keys
     */
    const MERCHANT_USER_ID = 'merchantUUID';
    const API_KEY = 'apiKey';
    /**#@-*/

    /**#@+
     * Post params
     */
    const METHOD = 'method';
    const TRANSACTION_ID = 'transactionID';
    const TRANSACTION_AMOUNT = 'transactionAmount';
    const TRANSACTION_CURRENCY = 'transactionCurrency';
    const REFUND_AMOUNT = 'refundAmount';
    /**#@-*/

    /**#@+
     * MerchantWarrior Api URL
     */
    const API_SANDBOX_URL = 'https://base.merchantwarrior.com/post/';
    const API_LIVE_URL = 'https://api.merchantwarrior.com/post/';
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
     */
    public function __construct(
        Config $config,
        HashGenerator $hashGenerator,
        ClientInterface $client,
        ManagerInterface $manager,
        TimezoneInterface $timezone,
        SerializerInterface $serializer
    ) {
        $this->config = $config;
        $this->hashGenerator = $hashGenerator;
        $this->client = $client;
        $this->eventManager = $manager;
        $this->timezone = $timezone;
        $this->serializer = $serializer;
    }

    /**
     * Get base API url
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        if ($this->config->isSandBoxModeEnabled()) {
            return self::API_SANDBOX_URL;
        }
        return self::API_LIVE_URL;
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

            $this->client->addHeader('MW-API-VERSION', '2.0');
            $this->client->addHeader('content-type', 'application/json');
            $this->client->setOption((string)CURLOPT_CUSTOMREQUEST, 'POST');

            $this->client->post($this->getApiUrl(), $this->serializer->serialize($data));

            $this->afterCall();
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
            $result = $this->serializer->unserialize($result);
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
    protected function getErrorMessage(string $key): ?string
    {
        if (!$this->getResponse($key)->isEmpty()
            && ($responseCode = $this->getResponse($key)->hasData('responseCode'))
        ) {
            if ($responseCode != 0) {
                return $this->getResponse($key)->getData('responseMessage');
            }
        }
        return null;
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
     * @return void
     */
    protected function afterCall(): void
    {
        $this->status = $this->client->getStatus();

        $this->eventManager->dispatch(
            'merchant_warrior_post_after',
            [
                'method'        => __METHOD__,
                'status'        => $this->status,
                'response_data' => $this->client->getBody(),
                'call_time'     => $this->getCallTime(),
            ]
        );
    }
}
