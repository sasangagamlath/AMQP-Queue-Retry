<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Api\Data;

interface QueueConfigInterface
{
    public const string CONFIG_ID = 'config_id';
    public const string QUEUE_NAME = 'queue_name';
    public const string EXCHANGE_NAME = 'exchange_name';
    public const string IS_ENABLED = 'is_enabled';
    public const string INITIAL_INTERVALS = 'initial_intervals';
    public const string DLQ_INTERVALS = 'dlq_intervals';
    public const string TOPOLOGY_APPLIED_AT = 'topology_applied_at';
    public const string APPLY_TOPOLOGY = 'apply_topology';

    /**
     * Get config id
     *
     * @return mixed
     */
    public function getConfigId();

    /**
     * Set config id
     *
     * @param int $configId
     * @return mixed
     */
    public function setConfigId(int $configId);

    /**
     * Get queue name
     *
     * @return string
     */
    public function getQueueName(): string;

    /**
     * Set queue name
     *
     * @param string $queueName
     * @return mixed
     */
    public function setQueueName(string $queueName);

    /**
     * Get exchange name
     *
     * @return string
     */
    public function getExchangeName(): string;

    /**
     * Set exchange name
     *
     * @param string $exchangeName
     * @return mixed
     */
    public function setExchangeName(string $exchangeName);

    /**
     * Get is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Set is enabled
     *
     * @param bool $isEnabled
     * @return mixed
     */
    public function setIsEnabled(bool $isEnabled);

    /**
     * Get initial intervals
     *
     * @return array
     */
    public function getInitialIntervals(): array;

    /**
     * Set initial intervals
     *
     * @param array $intervals
     * @return mixed
     */
    public function setInitialIntervals(array $intervals);

    /**
     * Get dlq intervals
     *
     * @return array
     */
    public function getDlqIntervals(): array;

    /**
     * Set dlq intervals
     *
     * @param array $intervals
     * @return mixed
     */
    public function setDlqIntervals(array $intervals);

    /**
     * Get topology applied at
     *
     * @return mixed
     */
    public function getTopologyAppliedAt();

    /**
     * Set topology applied at
     *
     * @param string|null $timestamp
     * @return null|mixed
     */
    public function setTopologyAppliedAt(?string $timestamp);
}
