<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\ViewModel;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\Campaign\ImageUploader;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;

/**
 * PATTERN: ViewModel — presentation getters only. Inject via layout argument name="view_model".
 */
class CampaignList implements ArgumentInterface
{
    /**
     * @var CampaignInterface[]|null
     */
    private ?array $items = null;

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly UrlInterface $urlBuilder,
        private readonly ImageUploader $imageUploader
    ) {
    }

    /**
     * @return CampaignInterface[]
     */
    public function getItems(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }
        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter((int)$this->storeManager->getStore()->getId());
        $collection->addVisibleFilter(new \DateTimeImmutable('now'));
        $collection->setOrder(CampaignInterface::START_AT, 'DESC');
        $this->items = array_values($collection->getItems());
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->getItems() === [];
    }

    public function getViewUrl(CampaignInterface $campaign): string
    {
        return $this->urlBuilder->getUrl('campaigns/index/view', ['id' => $campaign->getCampaignId()]);
    }

    public function getImageUrl(CampaignInterface $campaign): ?string
    {
        $image = $campaign->getImage();
        return $image ? $this->imageUploader->getMediaUrl($image) : null;
    }
}
