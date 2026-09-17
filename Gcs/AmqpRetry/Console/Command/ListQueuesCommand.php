<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Gcs\AmqpRetry\Api\QueueConfigRepositoryInterface;
use Gcs\AmqpRetry\Model\Intervals;

class ListQueuesCommand extends Command
{

    /**
     * @param QueueConfigRepositoryInterface $repository
     * @param Intervals $intervals
     */
    public function __construct(
        private readonly QueueConfigRepositoryInterface $repository,
        private readonly Intervals                      $intervals
    ) {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('queue:retry:list')
            ->setDescription('Output configured retry list');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(
            [
                'Queue Name',
                'Exchange',
                'Enabled',
                'Initial Intervals',
                'DLQ Intervals',
                'Topology Applied At'
            ]
        );

        foreach ($this->repository->getAll() as $config) {
            $table->addRow([
                $config->getQueueName(),
                $config->getExchangeName(),
                $config->isEnabled() ? 'Yes' : 'No',
                implode(',', $this->intervals->getInitialIntervals($config)),
                implode(',', $this->intervals->getDlqIntervals($config)),
                $config->getTopologyAppliedAt() ?: 'Never'
            ]);
        }
        $table->render();
        return Command::SUCCESS;
    }
}
