<?php

declare(strict_types=1);

namespace MerchantWarrior\Payment\Controller\Notification;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use MerchantWarrior\Payment\Model\Service\SaveToZipData;
use Psr\Log\LoggerInterface;

class Get implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var SaveToZipData
     */
    private $saveToZipData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param SaveToZipData $saveToZipData
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        SaveToZipData $saveToZipData,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->saveToZipData = $saveToZipData;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $data = $this->request->getParams();

        $this->logger->debug(json_encode($data));

        $resultJson = $this->jsonFactory->create();

        return $resultJson->setData(['result' => 'success']);
    }
}
