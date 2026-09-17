<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Controller\Adminhtml\Queue;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;

class Delete extends Action implements HttpGetActionInterface
{

    /**
     * Authorization level
     */
    public const string ADMIN_RESOURCE = 'Gcs_AmqpRetry::delete';

    /**
     * @param Context $context
     * @param QueueConfigRepositoryInterface $queueRepository
     */
    public function __construct(
        Context $context,
        protected QueueConfigRepositoryInterface $queueRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        $configId = (int)$this->getRequest()->getParam(QueueConfigInterface::CONFIG_ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirect = $resultRedirect->setPath('*/*/');

        if ($configId) {
            try {
                $this->queueRepository->deleteById($configId);
                $this->messageManager->addSuccessMessage(__('Record have been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $redirect = $resultRedirect->setPath('*/*/', ['id' => $configId]);
            }
        }
        return $redirect;
    }
}
