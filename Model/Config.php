<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'blueprint/general/enabled';
    private const XML_PATH_LIST_ENABLED = 'blueprint/general/list_enabled';
    private const XML_PATH_DEFAULT_PRODUCTS_COUNT = 'blueprint/general/default_products_count';
    private const XML_PATH_NOTIFICATION_EMAIL = 'blueprint/general/notification_email';
    private const XML_PATH_STORE_EMAIL = 'trans_email/ident_general/email';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isListEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LIST_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getDefaultProductsCount(?int $storeId = null): int
    {
        $value = (int)$this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_PRODUCTS_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value > 0 ? $value : 10;
    }

    public function getNotificationEmail(?int $storeId = null): string
    {
        $configured = (string)$this->scopeConfig->getValue(
            self::XML_PATH_NOTIFICATION_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($configured !== '') {
            return $configured;
        }
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_STORE_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
