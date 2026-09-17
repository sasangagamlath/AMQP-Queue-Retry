<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Controller\Adminhtml\Queue;

use Exception;
use Gcs\AmqpRetry\Model\ResourceModel\QueueConfig\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{

    /**
     * Authorization level
     */
    public const string ADMIN_RESOURCE = 'Gcs_AmqpRetry::delete';

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context                            $context,
        private readonly CollectionFactory $collectionFactory,
        private readonly Filter            $filter
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            $collection->walk('delete');
            $this->messageManager->addSuccessMessage(__("Queue Config's has been deleted."));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__("Something wrong when delete Queue Config's."));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
