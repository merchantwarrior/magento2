<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Test\Unit\Model\Service;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use MerchantWarrior\Payment\Api\Direct\GetSettlementInterface;
use MerchantWarrior\Payment\Model\Api\RequestApiInterface;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\Service\CancelStuckOrders;
use MerchantWarrior\Payment\Model\Service\GetSettlementData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    protected $orderRepository;

    /**
     * @var GetSettlementData|MockObject
     */
    protected $getSettlementData;

    /**
     * @var MockObject
     */
    protected $logger;

    /**
     * @var Collection|MockObject
     */
    protected $collection;

    /**
     * Initialize repository
     */
    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);
        $this->config = $this->createMock(Config::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->getSettlementData = $this->createMock(GetSettlementData::class);
        $this->logger = $this->createMock(LoggerInterface::class);

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
     * @param int $settlementDays
     * @param array $expected
     *
     * @dataProvider formGetFromToRangeProvider
     * @throws \ReflectionException
     */
    public function testGetFromToRange(string $date, int $settlementDays, array $expected): void
    {
        $this->config->expects($this->once())
            ->method('getSettlementDays')
            ->willReturn($settlementDays);

        $cancelStuckOrders = new CancelStuckOrders(
            $this->collection,
            $this->orderRepository,
            $this->getSettlementData,
            $this->config,
            $this->logger
        );

        $result = $this->invokeMethod($cancelStuckOrders, 'getFromToRange', [$date]);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provider formIsTransactionDeclinedProvider
     *
     * @return array
     */
    public function formGetFromToRangeProvider(): array
    {
        $params = [
            'case_1' => [
                'date' => '2020-01-01',
                'settlement_days' => Config::COUNT_SETTLEMENT_DAYS,
                'expected' => []
            ],
            'case_2' => [
                'date' => '2020-04-30',
                'settlement_days' => 2,
                'expected' => []
            ]
        ];

        $from = new \DateTime($params['case_1']['date']);

        $from->modify('-1 day');
        $to = clone $from;
        $to->modify('+' . $params['case_1']['settlement_days'] . ' day');

        $params['case_1']['expected'] = [
            'from' => $from->format(GetSettlementInterface::DATE_FORMAT),
            'to'   => $to->format(GetSettlementInterface::DATE_FORMAT)
        ];

        $from = new \DateTime($params['case_2']['date']);

        $from->modify('-1 day');
        $to = clone $from;
        $to->modify('+' . $params['case_2']['settlement_days'] . ' day');

        $params['case_2']['expected'] = [
            'from' => $from->format(GetSettlementInterface::DATE_FORMAT),
            'to'   => $to->format(GetSettlementInterface::DATE_FORMAT)
        ];

        return $params;
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
