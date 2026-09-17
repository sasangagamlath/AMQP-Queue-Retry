<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model;

use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItem\Iterator as QueueIterator;

class TopicToQueueResolver
{

    /**
     * @var null
     */
    private $map = null;

    /**
     * @param QueueIterator $queueIterator
     */
    public function __construct(
        private QueueIterator $queueIterator
    ) {
    }

    /**
     * Resolves a message topic name to the queue name it is bound to.
     *
     * @param string $topic
     * @return string|null
     */
    public function resolve(string $topic): ?string
    {
        if ($this->map === null) {
            $this->map = [];
            foreach ($this->queueIterator as $queueConfigItem) {
                $queueName = $queueConfigItem->getName();
                foreach ($queueConfigItem->getBindings() as $binding) {
                    $this->map[$binding->getTopic()] = $queueName;
                }
            }
        }
        return $this->map[$topic] ?? '';
    }
}
