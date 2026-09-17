<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Ui\Component\Listing\Column;

use Gcs\AmqpRetry\Api\Data\QueueConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    public const string URL_PATH_EDIT = 'amqpretry/queue/edit';
    public const string URL_PATH_DELETE = 'amqpretry/queue/delete';
    public const string URL_PATH_APPLY_TOPOLOGY = 'amqpretry/queue/applytopology';

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $configId = $item[QueueConfigInterface::CONFIG_ID];
                $queueName = $item[QueueConfigInterface::QUEUE_NAME];

                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['config_id' => $configId]),
                    'label' => __('Edit'),
                ];

                $item[$name]['apply_topology'] = [
                    'callback' => [
                        [
                            'provider' => 'amqpretry_queue_listing.amqp_retry_queue_listing_data_source',
                            'target' => 'applyTopology',
                        ],
                    ],
                    'params' => [
                        'action' => 'apply',
                        'config_id' => $configId,
                        'queue_name' => $queueName,
                        'ajax_url' => $this->urlBuilder->getUrl(self::URL_PATH_APPLY_TOPOLOGY),
                    ],
                    'href' => '#',
                    'label' => __('Apply Topology'),
                ];

                $item[$name]['delete_topology'] = [
                    'callback' => [
                        [
                            'provider' => 'amqpretry_queue_listing.amqp_retry_queue_listing_data_source',
                            'target' => 'deleteTopology',
                        ],
                    ],
                    'params' => [
                        'action' => 'delete',
                        'config_id' => $configId,
                        'queue_name' => $queueName,
                        'ajax_url' => $this->urlBuilder->getUrl(self::URL_PATH_APPLY_TOPOLOGY),
                    ],
                    'href' => '#',
                    'label' => __('Delete Topology'),
                ];

                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['config_id' => $configId]),
                    'label' => __('Delete Config'),
                    'confirm' => [
                        'title' => __('Delete Config'),
                        'message' => __('Are you sure you want to delete this row?')
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
