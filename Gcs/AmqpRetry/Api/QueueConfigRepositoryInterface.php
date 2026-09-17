<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Api;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;

interface QueueConfigRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $configId
     * @return QueueConfigInterface
     */
    public function getById(int $configId): QueueConfigInterface;

    /**
     * Get by queue name
     *
     * @param string $queueName
     * @return QueueConfigInterface
     */
    public function getByQueueName(string $queueName): QueueConfigInterface;

    /**
     * Find by queu name
     *
     * @param string $queueName
     * @return QueueConfigInterface|null
     */
    public function findByQueueName(string $queueName): ?QueueConfigInterface;

    /**
     * Get all
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Save
     *
     * @param QueueConfigInterface $queue
     * @return QueueConfigInterface
     */
    public function save(QueueConfigInterface $queue): QueueConfigInterface;

    /**
     * Delete
     *
     * @param QueueConfigInterface $queue
     * @return bool
     */
    public function delete(QueueConfigInterface $queue): bool;

    /**
     * Delete by id
     *
     * @param int $configId
     * @return bool
     */
    public function deleteById(int $configId): bool;
}
