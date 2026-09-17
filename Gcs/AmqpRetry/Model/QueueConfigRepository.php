<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Api\Data\QueueConfigInterfaceFactory;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Model\ResourceModel\QueueConfig as ResourceQueueConfig;
use Gcs\AmqpRetry\Model\ResourceModel\QueueConfig\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class QueueConfigRepository implements QueueConfigRepositoryInterface
{

    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @param QueueConfigInterfaceFactory $queueFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceQueueConfig $queueConfigResource
     */
    public function __construct(
        private readonly QueueConfigInterfaceFactory $queueFactory,
        protected readonly CollectionFactory         $collectionFactory,
        private readonly ResourceQueueConfig         $queueConfigResource,
    ) {
    }

    /**
     * Get queue by id
     *
     * @param int $configId
     * @return QueueConfigInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $configId): QueueConfigInterface
    {
        $queue = $this->queueFactory->create();
        $this->queueConfigResource->load($queue, $configId);
        if (!$queue->getId()) {
            throw new NoSuchEntityException(__('Queue with id "%1" does not exist.', $configId));
        }
        return $queue;
    }

    /**
     * Get by queue name
     *
     * @param string $queueName
     * @return QueueConfigInterface
     * @throws NoSuchEntityException
     */
    public function getByQueueName(string $queueName): QueueConfigInterface
    {
        $config = $this->findByQueueName($queueName);
        if (!$config) {
            throw new NoSuchEntityException(__('Queue for name %1 does not exist.', $queueName));
        }
        return $config;
    }

    /**
     * @inheritDoc
     *
     * @param string $queueName
     * @return QueueConfigInterface|null
     */
    public function findByQueueName(string $queueName): ?QueueConfigInterface
    {
        if (!isset($this->cache[$queueName])) {
            $config = $this->queueFactory->create();
            $this->queueConfigResource->load($config, $queueName, 'queue_name');
            if ($config->getId()) {
                $this->cache[$queueName] = $config;
            } else {
                $this->cache[$queueName] = null;
            }
        }
        return $this->cache[$queueName];
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->collectionFactory->create()->getItems();
    }

    /**
     * @inheritDoc
     *
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(QueueConfigInterface $queue): QueueConfigInterface
    {
        try {
            $this->queueConfigResource->save($queue);
            $this->invalidateCache($queue);
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('Could not save the data: %1', $e->getMessage()));
        }

        return $queue;
    }

    /**
     * @inheritdoc
     */
    public function delete(QueueConfigInterface $queue): bool
    {
        try {
            $this->queueConfigResource->delete($queue);
            $this->invalidateCache($queue);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the data: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     *
     * @throws CouldNotDeleteException|NoSuchEntityException
     */
    public function deleteById(int $configId): bool
    {
        return $this->delete($this->getById($configId));
    }

    /**
     * Invalidate cached queue
     *
     * @param QueueConfigInterface $queue
     * @return void
     */
    private function invalidateCache(QueueConfigInterface $queue): void
    {
        foreach ($this->cache as $queueName => $cachedQueue) {
            if ($cachedQueue === $queue) {
                unset($this->cache[$queueName]);
                continue;
            }
            if ($cachedQueue !== null
                && $cachedQueue->getId()
                && $cachedQueue->getId() === $queue->getId()
            ) {
                unset($this->cache[$queueName]);
            }
        }
        unset($this->cache[$queue->getQueueName()]);
    }
}
