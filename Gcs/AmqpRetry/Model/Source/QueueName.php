<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Iterator as ConsumerIterator;

class QueueName implements OptionSourceInterface
{
    /**
     * @param ConsumerIterator $consumerIterator
     */
    public function __construct(
        private readonly ConsumerIterator $consumerIterator
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->consumerIterator as $consumer) {
            $queue = $consumer->getQueue();
            $options[$queue] = [
                'value' => $queue,
                'label' => $queue
            ];
        }
        return array_values($options);
    }
}
