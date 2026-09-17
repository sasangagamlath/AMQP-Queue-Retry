<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Ui\DataProvider;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Model\ResourceModel\QueueConfig\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Serialize\Serializer\Json;

class QueueDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private array $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Json $json
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        private CollectionFactory $collectionFactory,
        private readonly Json     $json,
        array                     $meta = [],
        array                     $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Queue retry data provider collection
     *
     * @return array
     */
    public function getData(): array
    {
        if (!isset($this->loadedData)) {
            $this->loadedData = [];
            $queueCollection = $this->getCollection();

            foreach ($queueCollection->getItems() as $item) {
                $item->setData(
                    QueueConfigInterface::INITIAL_INTERVALS,
                    $this->json->unserialize($item->getData(QueueConfigInterface::INITIAL_INTERVALS))
                );
                $item->setData(
                    QueueConfigInterface::DLQ_INTERVALS,
                    $this->json->unserialize($item->getData(QueueConfigInterface::DLQ_INTERVALS))
                );

                $this->loadedData[$item->getData(QueueConfigInterface::CONFIG_ID)] = $item->getData();
            }
        }
        return $this->loadedData;
    }
}
