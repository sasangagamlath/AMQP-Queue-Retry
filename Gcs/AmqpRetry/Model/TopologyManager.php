<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Magento\Framework\Amqp\Config as AmqpConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class TopologyManager
{

    /**
     * @param QueueConfigRepositoryInterface $queueRepository
     * @param AmqpConfig $amqpConfig
     * @param DateTime $dateTime
     * @param Intervals $intervals
     * @param Config $config
     */
    public function __construct(
        private readonly QueueConfigRepositoryInterface $queueRepository,
        private readonly AmqpConfig                     $amqpConfig,
        private readonly DateTime                       $dateTime,
        private readonly Intervals                      $intervals,
        private readonly Config                         $config,
    ) {
    }

    /**
     * Apply topology
     *
     * @param QueueConfigInterface $queue
     * @return void
     * @throws LocalizedException
     */
    public function applyTopology(QueueConfigInterface $queue): void
    {
        $intervals = array_unique(
            array_merge(
                $this->intervals->getInitialIntervals($queue),
                $this->intervals->getDlqIntervals($queue)
            )
        );
        $channel = $this->amqpConfig->getChannel();
        $queueName = $queue->getQueueName();

        foreach ($intervals as $interval) {
            foreach (explode(',', $interval) as $d) {
                $d = (int)trim($d);
                $waitQueue = "{$queueName}.wait.{$d}s";

                $arguments = [
                    'x-message-ttl' => ['I', $d * 1000],
                    'x-dead-letter-exchange' => ['S', ''],
                    'x-dead-letter-routing-key' => ['S', $queueName]
                ];

                $channel->queue_declare(
                    $waitQueue,
                    false,
                    true,
                    false,
                    false,
                    false,
                    $arguments
                );
            }
        }

        $queue->setTopologyAppliedAt($this->dateTime->gmtDate());
        $this->queueRepository->save($queue);
    }

    /**
     * Delete topology
     *
     * @param QueueConfigInterface $queue
     * @return void
     * @throws LocalizedException
     */
    public function deleteTopology(QueueConfigInterface $queue): void
    {
        $intervals = array_unique(array_merge(
            $this->intervals->getInitialIntervals($queue),
            $this->intervals->getDlqIntervals($queue)
        ));
        $channel = $this->amqpConfig->getChannel();
        $queueName = $queue->getQueueName();

        foreach ($intervals as $interval) {
            foreach (explode(',', $interval) as $d) {
                $d = (int)trim($d);
                $waitQueue = "{$queueName}.wait.{$d}s";
                $channel->queue_delete($waitQueue, false, false);
            }
        }

        $queue->setTopologyAppliedAt(null);
        $this->queueRepository->save($queue);
    }
}
