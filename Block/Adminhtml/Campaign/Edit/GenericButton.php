<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Block\Adminhtml\Campaign\Edit;

use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    public function __construct(
        protected readonly Context $context
    ) {
    }

    public function getCampaignId(): int
    {
        return (int)$this->context->getRequest()->getParam('campaign_id');
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
