<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Controller\Adminhtml\Queue;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterfaceFactory;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action implements HttpGetActionInterface
{
    public const string ADMIN_RESOURCE = 'Gcs_AmqpRetry::edit';

    /**
     * @param Context $context
     * @param QueueConfigInterfaceFactory $queueFactory
     * @param QueueConfigRepositoryInterface $queueRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                         $context,
        private readonly QueueConfigInterfaceFactory    $queueFactory,
        private readonly QueueConfigRepositoryInterface $queueRepository,
        private readonly PageFactory $resultPageFactory
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
     * Edit action
     */
    public function execute(): Page
    {
        $configId = (int)$this->getRequest()->getParam(QueueConfigInterface::CONFIG_ID);

        if ($configId) {
            try {
                $queueConfig = $this->queueRepository->getById($configId);
            } catch (NoSuchEntityException) {
                $this->messageManager->addErrorMessage(__('This Queue no longer exists.'));
                $this->_redirect('*/*/');
            }
        } else {
            $queueConfig = $this->queueFactory->create();
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->getConfig()->getTitle()->prepend(__('Add/Edit Queue'));

        return $resultPage;
    }
}
