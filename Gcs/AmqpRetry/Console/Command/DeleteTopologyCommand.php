<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Console\Command;

use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Model\TopologyManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteTopologyCommand extends Command
{
    /**
     * @param QueueConfigRepositoryInterface $queueRepository
     * @param TopologyManager $topologyManager
     * @param string|null $name
     */
    public function __construct(
        private readonly QueueConfigRepositoryInterface $queueRepository,
        private readonly TopologyManager                $topologyManager,
        ?string                                         $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure():void
    {
        $this->setName('queue:retry:delete-topology')
            ->setDescription('Delete topology')
            ->addOption('queue', 'u', InputOption::VALUE_OPTIONAL, 'Queue name to delete.');
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $queueName = $input->getOption('queue');
        $queueData =
            $queueName ? [$this->queueRepository->getByQueueName($queueName)] : $this->queueRepository->getAll();

        foreach ($queueData as $queue) {
            try {
                $this->topologyManager->deleteTopology($queue);
                $output->writeln("<info>Successfully deleted queue: {$queue->getQueueName()}</info>");
            } catch (\Exception $e) {
                $output->writeln("<error>Failed deleted queue: {$queue->getQueueName()}: {$e->getMessage()}</error>");
            }
        }
        return Command::SUCCESS;
    }
}
