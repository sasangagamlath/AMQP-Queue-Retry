<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model\ResourceModel;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QueueConfig extends AbstractDb
{
    public const string TABLE_NAME = 'amqp_retry_queue_config';

    /**
     * @var string
     */
    protected string $_eventPrefix = 'amqp_retry_queue_resource_model';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, QueueConfigInterface::CONFIG_ID);
    }
}
