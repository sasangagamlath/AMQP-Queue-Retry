<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Controller\Adminhtml\Queue;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Model\QueueConfigFactory;
use Gcs\AmqpRetry\Model\TopologyManager;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action implements HttpPostActionInterface
{

    /**
     * Authorization level
     */
    public const string ADMIN_RESOURCE = 'Gcs_AmqpRetry::edit';

    /**
     * @param Context $context
     * @param QueueConfigRepositoryInterface $queueRepository
     * @param QueueConfigFactory $queueConfigFactory
     * @param TopologyManager $topologyManager
     */
    public function __construct(
        Context                                         $context,
        private readonly QueueConfigRepositoryInterface $queueRepository,
        private readonly QueueConfigFactory             $queueConfigFactory,
        private readonly TopologyManager                $topologyManager
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getPostValue()) {

            $configId = $this->getRequest()->getParam(QueueConfigInterface::CONFIG_ID);
            $queueConfigData = $configId ? $this->queueRepository->getById((int)$configId) :
                $this->queueConfigFactory->create();

            $queueConfigData->setQueueName($this->getRequest()->getParam(QueueConfigInterface::QUEUE_NAME));
            $queueConfigData->setExchangeName($this->getRequest()->getParam(QueueConfigInterface::EXCHANGE_NAME));
            $queueConfigData->setIsEnabled((bool)$this->getRequest()->getParam(QueueConfigInterface::IS_ENABLED));
            $applyTopology = $this->getRequest()->getParam(QueueConfigInterface::APPLY_TOPOLOGY);

            $initialIntervals = $this->getRequest()->getParam(QueueConfigInterface::INITIAL_INTERVALS);
            $queueConfigData->setInitialIntervals(
                is_array($initialIntervals) ? $initialIntervals : explode(',', (string)$initialIntervals)
            );

            $dqlIntervals = $this->getRequest()->getParam(QueueConfigInterface::DLQ_INTERVALS);
            $queueConfigData->setDlqIntervals(
                is_array($dqlIntervals) ? $dqlIntervals : explode(',', (string)$dqlIntervals)
            );

            try {
                $this->queueRepository->save($queueConfigData);
                $this->messageManager->addSuccessMessage(__('The data has been saved.'));
                if ($applyTopology === "true") {
                    $queueData = $this->queueRepository->getById((int)$queueConfigData->getConfigId());
                    $this->topologyManager->applyTopology($queueData);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
                if (str_contains($e->getMessage(), 'Unique constraint violation')) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Queue with name "%1" already exists.', $queueConfigData->getQueueName())
                    );
                }
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
