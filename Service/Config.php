<?php

declare(strict_types=1);

namespace WeProvide\Core\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /** @var string */
    public const XML_PATH_DEV_LOG_LEVEL = 'dev/log/level';

    /** @var ScopeConfigInterface */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Returns the log level.
     *
     * @return string
     */
    public function getLogLevel(): string
    {
        return (string) $this->scopeConfig->getValue(static::XML_PATH_DEV_LOG_LEVEL);
    }
}
