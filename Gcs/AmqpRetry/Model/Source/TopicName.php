<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItem\Iterator as QueueIterator;

class TopicName implements OptionSourceInterface
{

    /**
     * @param QueueIterator $queueIterator
     */
    public function __construct(
        private readonly QueueIterator $queueIterator
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->queueIterator as $queue) {
            $queueName = $queue->getName();
            foreach ($queue->getBindings() as $binding) {
                $topic = $binding->getTopic();
                $options[$topic] = [
                    'value' => $topic,
                    'label' => "{$topic} (queue: {$queueName})"
                ];
            }
        }
        return array_values($options);
    }
}
