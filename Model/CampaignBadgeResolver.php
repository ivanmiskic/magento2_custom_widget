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

use Magento\Store\Model\StoreManagerInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;

/**
 * Maps product ids to the first matching visible campaign title, once per request.
 */
class CampaignBadgeResolver
{
    /**
     * @var array<int, string>|null
     */
    private ?array $productTitles = null;

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly ProductCollectionBuilder $productCollectionBuilder,
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config
    ) {
    }

    public function getBadgeLabel(int $productId): ?string
    {
        $map = $this->getProductCampaignTitles();
        return $map[$productId] ?? null;
    }

    /**
     * @return array<int, string>
     */
    private function getProductCampaignTitles(): array
    {
        if ($this->productTitles !== null) {
            return $this->productTitles;
        }
        $this->productTitles = [];
        if (!$this->config->isEnabled()) {
            return $this->productTitles;
        }

        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter((int)$this->storeManager->getStore()->getId());
        $collection->addVisibleFilter(new \DateTimeImmutable('now'));

        foreach ($collection as $campaign) {
            if (!$campaign->getConditionsSerialized()) {
                continue;
            }
            $products = $this->productCollectionBuilder->build(
                $campaign->getConditionsSerialized(),
                $campaign->getSortBy(),
                $campaign->getSortOrder(),
                $campaign->getProductsCount()
            );
            foreach ($products as $product) {
                $productId = (int)$product->getId();
                if (!isset($this->productTitles[$productId])) {
                    $this->productTitles[$productId] = (string)$campaign->getTitle();
                }
            }
        }
        return $this->productTitles;
    }
}
