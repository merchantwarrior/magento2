<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Test\Unit\Model\Api\Payframe;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Xml\Parser;
use MerchantWarrior\Payment\Api\Direct\ProcessAuthInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Api\Payframe\ProcessInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;
use MerchantWarrior\Payment\Model\Api\Payframe\Process;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\HashGenerator;
use MerchantWarrior\Payment\Model\Service\SaveToZipData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ProcessTest
 */
class ProcessTest extends TestCase
{
    public const API_PASSPHRASE = 'test';
    public const API_KEY = 'test';
    public const API_UUID = 'test';

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var ClientInterface|MockObject
     */
    protected $client;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezone;

    /**
     * @var SerializerInterface|MockObject
     */
    protected $serializer;

    /**
     * @var HashGenerator|MockObject
     */
    protected $hashGenerator;

    /**
     * @var SaveToZipData|MockObject
     */
    protected $saveToZipData;

    /**
     * @var Parser|MockObject
     */
    protected $xmlParser;

    /**
     * Initialize repository
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->hashGenerator = $this->createMock(HashGenerator::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->timezone = $this->createMock(TimezoneInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->saveToZipData = $this->createMock(SaveToZipData::class);
        $this->xmlParser = $this->createMock(Parser::class);
    }

    /**
     * Test for execute validation function
     *
     * @param string $method
     * @param array $transactionParams
     * @param string $expected
     *
     * @dataProvider formTestExecuteValidationProvider
     */
    public function testExecuteValidation(string $method, array $transactionParams, string $expected): void
    {
        $this->config->expects($this->once())
            ->method('isPayFrameActive')
            ->willReturn(true);

        $process = new Process(
            $this->config,
            $this->hashGenerator,
            $this->client,
            $this->eventManager,
            $this->timezone,
            $this->serializer,
            $this->saveToZipData,
            $this->xmlParser
        );

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($expected);
        $process->execute($method, $transactionParams);
    }

    /**
     * Provider testFormExecuteProvider
     *
     * @return array
     */
    public function formTestExecuteValidationProvider(): array
    {
        return [
            'case_1_empty_token' => [
                'method' => ProcessInterface::API_METHOD_CARD,
                'params' => [
                    RequestApiInterface::PAYFRAME_KEY => 'Test',
                    RequestApiInterface::PAYFRAME_TOKEN => '',
                    RequestApiInterface::TRANSACTION_AMOUNT => '100',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ],
                'expected' => 'Your card is incorrect!'
            ],
            'case_2_dont_isset_token' => [
                'method' => ProcessInterface::API_METHOD_CARD,
                'params' => [
                    RequestApiInterface::PAYFRAME_KEY => 'Test',
                    RequestApiInterface::TRANSACTION_AMOUNT => '100',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ],
                'expected' => 'Your card is incorrect!'
            ],
            'case_3_empty_transaction_data' => [
                'method' => ProcessInterface::API_METHOD_CARD,
                'params' => [
                    RequestApiInterface::PAYFRAME_KEY => 'Test',
                    RequestApiInterface::PAYFRAME_TOKEN => 'Test',
                    RequestApiInterface::TRANSACTION_AMOUNT => '',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ],
                'expected' => 'You must enter correct transaction data!'
            ],
            'case_4_dont_isset_transaction_data' => [
                'method' => ProcessInterface::API_METHOD_CARD,
                'params' => [
                    RequestApiInterface::PAYFRAME_KEY => 'Test',
                    RequestApiInterface::PAYFRAME_TOKEN => 'Test',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ],
                'expected' => 'You must enter correct transaction data!'
            ]
        ];
    }

    /**
     * Test Form data method
     *
     * @param array $params
     * @param array $expected
     *
     * @return void
     * @dataProvider formTestFormDataProvider
     * @throws \ReflectionException
     */
    public function testFormData(array $params, array $expected): void
    {
        $this->config->method('getApiKey')->willReturn(self::API_PASSPHRASE);
        $this->config->method('getPassPhrase')->willReturn(self::API_PASSPHRASE);
        $this->config->method('getMerchantUserId')->willReturn(self::API_UUID);

        $process = new Process(
            $this->config,
            new HashGenerator($this->config),
            $this->client,
            $this->eventManager,
            $this->timezone,
            $this->serializer,
            $this->saveToZipData,
            $this->xmlParser
        );

        $result = $this->invokeMethod($process, 'formData', [$params]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Provider formTestFormDataProvider
     *
     * @return array
     */
    public function formTestFormDataProvider(): array
    {
        return [
            'case_1_process_void' => [
                'params' => [
                    RequestApiInterface::METHOD => ProcessVoidInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_ID => '1-1-1-1'
                ],
                'expected' => [
                    'hash' => md5(strtolower(md5(self::API_PASSPHRASE) . self::API_UUID . '1-1-1-1')),
                    'merchantUUID' => self::API_UUID,
                    'apiKey' => self::API_KEY,
                    RequestApiInterface::METHOD => ProcessVoidInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_ID => '1-1-1-1'
                ]
            ],
            'case_2_process_auth' => [
                'params' => [
                    RequestApiInterface::METHOD => ProcessAuthInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_AMOUNT   => '100',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ],
                'expected' => [
                    'hash' =>  md5(
                        strtolower(
                            md5(self::API_PASSPHRASE) . self::API_UUID . '100' . 'AUD'
                        )
                    ),
                    'merchantUUID' => self::API_UUID,
                    'apiKey' => self::API_KEY,
                    RequestApiInterface::METHOD => ProcessAuthInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_AMOUNT   => '100',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ]
            ]
        ];
    }

    /**
     * Invoke private method
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
