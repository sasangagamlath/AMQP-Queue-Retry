<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Plugin;

use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Model\RetryPublisher;
use Magento\Framework\Amqp\Queue;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\EnvelopeInterface;

class QueueRejectPlugin
{
    /**
     * @param QueueConfigRepositoryInterface $repository
     * @param RetryPublisher $publisher
     */
    public function __construct(
        private readonly QueueConfigRepositoryInterface $repository,
        private readonly RetryPublisher $publisher
    ) {
    }

    /**
     * Around reject
     *
     * @param Queue $subject
     * @param callable $proceed
     * @param EnvelopeInterface $message
     * @param bool $requeue
     * @return void
     * @throws ConnectionLostException
     */
    public function aroundReject(
        Queue $subject,
        callable $proceed,
        EnvelopeInterface $message,
        bool $requeue = true
    ): void {
        $queueName = $subject->getQueueName();
        $config = $this->repository->findByQueueName($queueName);

        if (!$config || !$config->isEnabled()) {
            $proceed($message, $requeue);
            return;
        }

        $properties = $message->getProperties();
        $headers = $properties['application_headers'] ?? [];
        $retryCount = isset($headers['x-retry-count']) ? (int)$headers['x-retry-count'] : 0;

        $initialIntervals = $config->getInitialIntervals();
        $dlqIntervals = $config->getDlqIntervals();

        if ($retryCount < count($initialIntervals)) {
            $delay = $initialIntervals[$retryCount];
        } elseif ($retryCount < (count($initialIntervals) + count($dlqIntervals))) {
            $delay = $dlqIntervals[$retryCount - count($initialIntervals)];
        } else {
            $proceed($message, $requeue);
            return;
        }

        $waitQueueName = "{$queueName}.wait.{$delay}s";
        $this->publisher->publish($message, $config->getExchangeName(), $delay, $waitQueueName);
        $subject->acknowledge($message);
    }
}
