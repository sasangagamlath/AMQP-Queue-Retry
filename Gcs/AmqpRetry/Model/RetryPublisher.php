<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model;

use Magento\Framework\Amqp\Config as AmqpConfig;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use PhpAmqpLib\Message\AMQPMessage;

class RetryPublisher
{

    /**
     * @param AmqpConfig $config
     */
    public function __construct(
        private readonly AmqpConfig $config
    ) {
    }

    /**
     * Publish
     *
     * @param EnvelopeInterface $message
     * @param string $exchangeName
     * @param int $delaySeconds
     * @param string $waitQueueName
     * @return void
     */
    public function publish(
        EnvelopeInterface $message,
        string $exchangeName,
        int $delaySeconds,
        string $waitQueueName
    ): void {
        $properties = $message->getProperties();
        $headers = $properties['application_headers'] ?? [];

        $retryCount = isset($headers['x-retry-count']) ? (int)$headers['x-retry-count'] : 0;
        $retryCount++;

        $headers['x-retry-count'] = $retryCount;
        $properties['application_headers'] = $headers;

        $amqpMessage = new AMQPMessage($message->getBody(), $properties);
        $channel = $this->config->getChannel();

        $channel->basic_publish($amqpMessage, $exchangeName, $waitQueueName);
    }
}
