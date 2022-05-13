<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Test\Unit\Model\Service;

use MerchantWarrior\Payment\Model\Api\RequestApiInterface;
use MerchantWarrior\Payment\Model\Service\CancelStuckOrders;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class HashGeneratorTest
 */
class CancelStuckOrdersTest extends TestCase
{
    /**
     * @var CancelStuckOrders|MockObject
     */
    protected $cancelStuckOrders;

    /**
     * Initialize repository
     */
    protected function setUp(): void
    {
        /**
         * Init mock for CancelStuckOrders
         */
        $this->cancelStuckOrders = $this->getMockBuilder(CancelStuckOrders::class)
            ->disableOriginalConstructor(true)
            ->getMock();
    }

    /**
     * Test for IsTransactionDeclined function
     *
     * @param string $incrementId
     * @param array $transactions
     * @param bool $expected
     *
     * @dataProvider formIsTransactionDeclinedProvider
     * @throws \ReflectionException
     */
    public function testIsTransactionDeclined(string $incrementId, array $transactions, bool $expected): void
    {
        $result = $this->invokeMethod(
            $this->cancelStuckOrders,
            'isTransactionDeclined',
            [$incrementId, $transactions]
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Provider formIsTransactionDeclinedProvider
     *
     * @return array
     */
    public function formIsTransactionDeclinedProvider(): array
    {
        return [
            'case_1_true' => [
                'incrementId' => '0001',
                'transactions' => [
                    '0001' => [
                        'statuses' => [
                            RequestApiInterface::STATUS_PREAUTH,
                            RequestApiInterface::STATUS_VOID
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_2_process_auth' => [
                'incrementId' => '0001',
                'transactions' => [
                    '0001' => [
                        'statuses' => [
                            RequestApiInterface::STATUS_PREAUTH
                        ]
                    ]
                ],
                'expected' => false
            ]
        ];
    }

    /**
     * Test for formGetFromToRangeProvider function
     *
     * @param string $date
     * @param array $expected
     *
     * @dataProvider formGetFromToRangeProvider
     * @throws \ReflectionException
     */
    public function testGetFromToRange(string $date, array $expected): void
    {
        $result = $this->invokeMethod($this->cancelStuckOrders, 'getFromToRange', [$date]);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provider formIsTransactionDeclinedProvider
     *
     * @return array
     */
    public function formGetFromToRangeProvider(): array
    {
        return [
            'case_1' => [
                'date' => '2020-01-01',
                'expected' => [
                    'from' => '2019-12-31',
                    'to' => '2020-01-07'
                ]
            ],
            'case_2' => [
                'date' => '2020-04-30',
                'expected' => [
                    'from' => '2020-04-29',
                    'to' => '2020-05-06'
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
