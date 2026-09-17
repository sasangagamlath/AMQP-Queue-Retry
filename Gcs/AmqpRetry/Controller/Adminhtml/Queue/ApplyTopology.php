<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Controller\Adminhtml\Queue;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Model\TopologyManager;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ApplyTopology extends Action implements HttpPostActionInterface
{

    /**
     * Authorization level
     */
    public const string ADMIN_RESOURCE = 'Gcs_AmqpRetry::topology';

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param TopologyManager $topologyManager
     * @param QueueConfigRepositoryInterface $queueRepository
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context                                         $context,
        private readonly JsonFactory                    $resultJsonFactory,
        private readonly TopologyManager                $topologyManager,
        private readonly QueueConfigRepositoryInterface $queueRepository,
        private readonly TimezoneInterface              $timezone,
        private readonly LoggerInterface                $logger,
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        $action = $this->getRequest()->getParam('action');
        $configId = (int)$this->getRequest()->getParam(QueueConfigInterface::CONFIG_ID);
        try {
            $queueData = $this->queueRepository->getById($configId);
            switch ($action) {
                case 'apply':
                    $this->topologyManager->applyTopology($queueData);
                    $message = __('Topology applied for the queue: %1', $configId);
                    break;

                case 'delete':
                    $this->topologyManager->deleteTopology($queueData);
                    $message = __('Topology deleted for the queue: %1', $configId);
                    break;

                default:
                    throw new LocalizedException(
                        __('Invalid topology action: %1', $action)
                    );
            }

            $queueData = $this->queueRepository->getById($configId);
            $topologyAppliedAt = $queueData->getTopologyAppliedAt() ? $this->timezone->formatDate(
                $queueData->getTopologyAppliedAt(),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::MEDIUM
            ) : null;

            return $result->setData([
                'success' => true,
                'message' => $message,
                'topology_applied_at' => $topologyAppliedAt,
            ]);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (LocalizedException $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            $this->logger->critical($e);
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while applying the topology. Please check the logs.'),
            ]);
        }
    }
}
