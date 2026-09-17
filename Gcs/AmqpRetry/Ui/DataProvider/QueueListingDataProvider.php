<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Ui\DataProvider;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Gcs\AmqpRetry\Model\ResourceModel\QueueConfig\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Ui\DataProvider\AbstractDataProvider;

class QueueListingDataProvider extends AbstractDataProvider
{

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
        string                             $name,
        string                             $primaryFieldName,
        string                             $requestFieldName,
        private readonly CollectionFactory $collectionFactory,
        private readonly Json              $json,
        array                              $meta = [],
        array                              $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Queue Retry data provider
     *
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();

        foreach ($collection as $item) {
            $item->setData(
                QueueConfigInterface::INITIAL_INTERVALS,
                $this->json->unserialize($item->getData(QueueConfigInterface::INITIAL_INTERVALS))
            );
            $item->setData(
                QueueConfigInterface::DLQ_INTERVALS,
                $this->json->unserialize($item->getData(QueueConfigInterface::DLQ_INTERVALS))
            );
        }

        return [
            'totalRecords' => $collection->getSize(),
            'items' => array_values($collection->toArray()['items'] ?? [])
        ];
    }
}
