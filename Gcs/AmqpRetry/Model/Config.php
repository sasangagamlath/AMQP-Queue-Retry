<?php

declare(strict_types=1);

namespace Gcs\AmqpRetry\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{

    public const string XML_PATH_AMQP_RETRY_DEFAULTS_INITIAL_INTERVALS = 'amqpretry/defaults/initial_intervals';
    public const string XML_PATH_AMQP_RETRY_DEFAULTS_DLQ_INTERVALS = 'amqpretry/defaults/dlq_intervals';
    public const string XML_PATH_AMQP_RETRY_DEFAULTS_EXCHANGE_NAME = 'amqpretry/defaults/exchange_name';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Get default config values
     *
     * @param string $xmlPath
     * @param mixed $storeId
     * @return string
     */
    public function getConfig(string $xmlPath, ?int $storeId = null): string
    {
        return $this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
