<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Model\TopologyManager;

class ApplyTopologyCommand extends Command
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
        $this->setName('queue:retry:apply-topology')
        ->setDescription('Apply topology')
            ->addOption('queue', 'u', InputOption::VALUE_OPTIONAL, 'Queue name to apply.');
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
            if (!$queue->isEnabled()) {
                continue;
            }
            try {
                $this->topologyManager->applyTopology($queue);
                $output->writeln("<info>Successfully run queue: {$queue->getQueueName()}</info>");
            } catch (\Exception $e) {
                $output->writeln("<error>Failed queue profile execution on: {
                $queue->getQueueName()}: {$e->getMessage()}</error>");
            }
        }
        return Command::SUCCESS;
    }
}
