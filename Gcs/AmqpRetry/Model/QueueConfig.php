<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Model\ResourceModel\QueueConfig as ResourceModelQueueConfig;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Registry;

class QueueConfig extends AbstractModel implements QueueConfigInterface, IdentityInterface
{

    protected const string CACHE_TAG = 'gcs_amqpretry';

    /**
     * Initialize resource model
     */
    public function _construct()
    {
        $this->_init(ResourceModelQueueConfig::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModelQueueConfig $resourceModelQueueConfig
     * @param Json $json
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        Context                                   $context,
        Registry                                  $registry,
        private readonly ResourceModelQueueConfig $resourceModelQueueConfig,
        private readonly Json                     $json,
        ?AbstractResource                         $resource = null,
        ?AbstractDb                               $resourceCollection = null,
        array                                     $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritDoc
     */
    public function getConfigId()
    {
        $value = $this->getData(self::CONFIG_ID);
        return $value !== null ? (int) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function setConfigId(int $configId): void
    {
        $this->setData(self::CONFIG_ID, $configId);
    }

    /**
     * @inheritDoc
     */
    public function getQueueName(): string
    {
        return (string)$this->getData(self::QUEUE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setQueueName(string $queueName): void
    {
        $this->setData(self::QUEUE_NAME, $queueName);
    }

    /**
     * @inheritDoc
     */
    public function getExchangeName(): string
    {
        return (string)$this->getData(self::EXCHANGE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setExchangeName(string $exchangeName): void
    {
        $this->setData(self::EXCHANGE_NAME, $exchangeName);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getData(self::IS_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsEnabled(bool $isEnabled): void
    {
        $this->setData(self::IS_ENABLED, $isEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getInitialIntervals(): array
    {
        $value = $this->getData(self::INITIAL_INTERVALS);
        return $value !== null && $value !== '' ? $this->json->unserialize($value) : [];
    }

    /**
     * @inheritDoc
     */
    public function setInitialIntervals(array $intervals): void
    {
        $this->setData(self::INITIAL_INTERVALS, $this->json->serialize($intervals));
    }

    /**
     * @inheritDoc
     */
    public function getDlqIntervals(): array
    {
        $value = $this->getData(self::DLQ_INTERVALS);
        return $value !== null && $value !== '' ? $this->json->unserialize($value) : [];
    }

    /**
     * @inheritDoc
     */
    public function setDlqIntervals(array $dlqIntervals): void
    {
        $this->setData(self::DLQ_INTERVALS, $this->json->serialize($dlqIntervals));
    }

    /**
     * @inheritDoc
     */
    public function getTopologyAppliedAt(): string
    {
        return (string)$this->getData(self::TOPOLOGY_APPLIED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setTopologyAppliedAt(?string $timestamp): void
    {
        $this->setData(self::TOPOLOGY_APPLIED_AT, $timestamp);
    }
}
