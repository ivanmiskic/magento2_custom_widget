<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Observer;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\CacheContext;
use Null\Blueprint\Api\Data\CampaignInterface;

/**
 * PATTERN: Observer — listen for null_blueprint_campaign_save_after and flush campaign + widget tags.
 */
class InvalidateCampaignCache implements ObserverInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly CacheContext $cacheContext,
        private readonly TypeListInterface $cacheTypeList
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $campaign = $observer->getEvent()->getData('campaign');
        $tags = [CampaignInterface::CACHE_TAG, CampaignInterface::WIDGET_CACHE_TAG];
        if ($campaign instanceof CampaignInterface && $campaign->getCampaignId()) {
            $tags[] = CampaignInterface::CACHE_TAG . '_' . $campaign->getCampaignId();
        }
        $this->cacheContext->registerTags($tags);
        $this->cache->clean($tags);
        $this->cacheTypeList->invalidate('full_page');
        $this->cacheTypeList->invalidate('block_html');
    }
}
