<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Gateway\Response;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use MerchantWarrior\Payment\Logger\MerchantWarriorLogger;
use MerchantWarrior\Payment\Model\Config;
use MerchantWarrior\Payment\Model\Ui\PayFrame\ConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;

class VaultDetailsHandler extends AbstractHandler
{
    /**
     * @var PaymentTokenFactoryInterface
     */
    private PaymentTokenFactoryInterface $paymentTokenFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    private PaymentTokenManagementInterface $paymentTokenManagement;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * VaultDetailsHandler constructor.
     *
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param Config $config
     * @param SerializerInterface $serializer
     * @param Session $checkoutSession
     * @param MerchantWarriorLogger $logger
     */
    public function __construct(
        PaymentTokenFactoryInterface $paymentTokenFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Config $config,
        SerializerInterface $serializer,
        Session $checkoutSession,
        MerchantWarriorLogger $logger
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->config = $config;
        $this->serializer = $serializer;
        parent::__construct($checkoutSession, $logger);
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($response['responseCode']) || $response['responseCode'] !== '0') {
            return;
        }

        $paymentDO = $this->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if (isset($response['cardID'])
            && $this->isTokenExists($response['cardID'], (int)$paymentDO->getOrder()->getCustomerId())
        ) {
            return;
        }

        $paymentToken = $this->getVaultPaymentToken($response);
        if (null !== $paymentToken) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param array $response
     *
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken(array $response): ?PaymentTokenInterface
    {
        if (!isset($response['cardID'])) {
            return null;
        }

        try {
            $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
            $paymentToken->setGatewayToken($response['cardID']);
            $paymentToken->setExpiresAt($this->getExpirationDate($response));

            $paymentToken->setTokenDetails(
                $this->convertDetailsToJSON(
                    [
                        'type' => $this->getCreditCardType($response['cardType'], 'name'),
                        'maskedCC' => $this->formCardNumber($response['paymentCardNumber']),
                        'expirationDate' => $response['cardExpiryMonth'] . '/' . $response['cardExpiryYear'],
                        'cardKey' => $response['cardKey'],
                        'ivrCardID' => $response['ivrCardID'],
                        'code_alt' => $this->getCreditCardType($response['cardType'], 'code_alt')
                    ]
                )
            );
            return $paymentToken;
        } catch (NoSuchEntityException | \Exception $err) {
            $this->logger->error($err->getMessage());

            return null;
        }
    }

    /**
     * CHeck is token exists
     *
     * @param string $hash
     * @param int $customerId
     *
     * @return PaymentTokenInterface|null
     */
    private function isTokenExists(string $hash, int $customerId): ?PaymentTokenInterface
    {
        return $this->paymentTokenManagement->getByGatewayToken(
            $hash,
            ConfigProvider::METHOD_CODE,
            $customerId
        );
    }

    /**
     * Form Card Type
     *
     * @param string $cardType
     * @param string $key
     *
     * @return string
     */
    private function getCreditCardType(string $cardType, string $key): string
    {
        $cardsTypes = $this->config->getCcTypes();

        $result = $cardType;
        array_walk($cardsTypes, static function(&$card) use (&$result, $cardType, $key) {
            if ($card['code_alt'] === $cardType) {
                $result = $card[$key];
            }
        });
        return $result;
    }

    /**
     * @param array $response
     *
     * @return string
     * @throws Exception
     * @throws Exception
     */
    private function getExpirationDate(array $response): string
    {
        $expDate = new DateTime(
            $response['cardExpiryYear']
            . '-'
            . $response['cardExpiryMonth']
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new DateTimeZone('UTC')
        );
        $expDate->add(new DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Convert payment token details to JSON
     *
     * @param array $details
     *
     * @return string
     */
    private function convertDetailsToJSON(array $details): string
    {
        $json = $this->serializer->serialize($details);
        return $json ?: '{}';
    }

    /**
     * Form card number
     *
     * @param string $cardNumber
     *
     * @return string
     */
    private function formCardNumber(string $cardNumber): string
    {
        return substr($cardNumber, -4);
    }

    /**
     * Get payment extension attributes
     *
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
