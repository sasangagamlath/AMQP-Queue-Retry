<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model\ResourceModel\QueueConfig;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Model\QueueConfig;
use Gcs\AmqpRetry\Model\ResourceModel\QueueConfig as ResourceModelQueueConfig;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = QueueConfigInterface::CONFIG_ID;

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            QueueConfig::class,
            ResourceModelQueueConfig::class
        );
    }
}
