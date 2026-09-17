<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model;

use Gcs\AmqpRetry\Model\QueueConfig;

class Intervals
{

    /**
     * @param Config $config
     */
    public function __construct(
        private readonly Config $config,
    ) {
    }

    /**
     * Get initial intervals
     *
     * @param QueueConfig $queue
     * @return array
     */
    public function getInitialIntervals(QueueConfig $queue) :array
    {
        return array_filter($queue->getInitialIntervals()) ?: explode(
            ',',
            $this->config->getConfig(Config::XML_PATH_AMQP_RETRY_DEFAULTS_INITIAL_INTERVALS)
        );
    }

    /**
     * Get dql intervals
     *
     * @param QueueConfig $queue
     * @return array
     *
     * Get dlq intervals
     */
    public function getDlqIntervals(QueueConfig $queue): array
    {
        return array_filter($queue->getDlqIntervals()) ?: explode(
            ',',
            $this->config->getConfig(Config::XML_PATH_AMQP_RETRY_DEFAULTS_DLQ_INTERVALS)
        );
    }
}
