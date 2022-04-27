<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Test\Unit\Model;

use MerchantWarrior\Payment\Api\Direct\ProcessAuthInterface;
use MerchantWarrior\Payment\Api\Direct\ProcessVoidInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;
use MerchantWarrior\Payment\Model\HashGenerator;
use MerchantWarrior\Payment\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class HashGeneratorTest
 */
class HashGeneratorTest extends TestCase
{
    public const API_PASSPHRASE = 'test';
    public const API_UUID = 'test';

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * Initialize repository
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
    }

    /**
     * Test for execute function
     *
     * @param array $params
     * @param string $expected
     *
     * @dataProvider formExecuteProvider
     */
    public function testExecute(array $params, string $expected): void
    {
        $this->config->expects($this->any())
            ->method('getPassPhrase')
            ->willReturn(self::API_PASSPHRASE);

        $this->config->expects($this->any())
            ->method('getMerchantUserId')
            ->willReturn(self::API_UUID);

        $hasGenerator = new HashGenerator(
            $this->config
        );

        $result = $hasGenerator->execute($params);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provider testFormExecuteProvider
     *
     * @return array
     */
    public function formExecuteProvider(): array
    {
        return [
            'case_1_process_void' => [
                'params' => [
                    RequestApiInterface::METHOD => ProcessVoidInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_ID => '1-1-1-1'
                ],
                'expected' => md5(strtolower(md5(self::API_PASSPHRASE) . self::API_UUID . '1-1-1-1'))
            ],
            'case_2_process_auth' => [
                'params' => [
                    RequestApiInterface::METHOD => ProcessAuthInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_AMOUNT   => '100',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ],
                'expected' => md5(
                    strtolower(
                        md5(self::API_PASSPHRASE) . self::API_UUID . '100' . 'AUD'
                    )
                )
            ],
            'case_3_empty_method' => [
                'params' => [
                    RequestApiInterface::METHOD => '',
                    RequestApiInterface::TRANSACTION_AMOUNT   => '100',
                    RequestApiInterface::TRANSACTION_CURRENCY => 'AUD'
                ],
                'expected' => ''
            ],
            'case_4_empty_currency' => [
                'params' => [
                    RequestApiInterface::METHOD => ProcessAuthInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_AMOUNT   => '100',
                    RequestApiInterface::TRANSACTION_CURRENCY => ''
                ],
                'expected' => md5(
                    strtolower(
                        md5(self::API_PASSPHRASE) . self::API_UUID . '100'
                    )
                )
            ],
            'case_5_not_set_currency' => [
                'params' => [
                    RequestApiInterface::METHOD => ProcessAuthInterface::API_METHOD,
                    RequestApiInterface::TRANSACTION_AMOUNT   => '100'
                ],
                'expected' => md5(
                    strtolower(
                        md5(self::API_PASSPHRASE) . self::API_UUID . '100'
                    )
                )
            ]
        ];
    }
}
