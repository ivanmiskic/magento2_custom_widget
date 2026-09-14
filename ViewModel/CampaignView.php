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

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\Campaign\ImageUploader;
use Null\Blueprint\Model\CampaignVisibility;
use Null\Blueprint\Model\ProductCollectionBuilder;

class CampaignView implements ArgumentInterface
{
    private ?CampaignInterface $campaign = null;

    private bool $loaded = false;

    public function __construct(
        private readonly RequestInterface $request,
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignVisibility $campaignVisibility,
        private readonly ProductCollectionBuilder $productCollectionBuilder,
        private readonly ImageUploader $imageUploader
    ) {
    }

    public function getCampaign(): ?CampaignInterface
    {
        if ($this->loaded) {
            return $this->campaign;
        }
        $this->loaded = true;
        $campaignId = (int)$this->request->getParam('id');
        if (!$campaignId) {
            return null;
        }
        try {
            $campaign = $this->campaignRepository->getById($campaignId);
        } catch (NoSuchEntityException $exception) {
            return null;
        }
        if (!$this->campaignVisibility->isVisible(
            $campaign->getStatus(),
            $campaign->getStartAt(),
            $campaign->getEndAt(),
            new \DateTimeImmutable('now')
        )) {
            return null;
        }
        $this->campaign = $campaign;
        return $this->campaign;
    }

    public function isEmpty(): bool
    {
        return $this->getCampaign() === null;
    }

    public function getTitle(): string
    {
        $campaign = $this->getCampaign();
        return $campaign ? (string)$campaign->getTitle() : '';
    }

    public function getDescription(): string
    {
        $campaign = $this->getCampaign();
        return $campaign ? (string)$campaign->getDescription() : '';
    }

    public function getImageUrl(): ?string
    {
        $campaign = $this->getCampaign();
        if (!$campaign || !$campaign->getImage()) {
            return null;
        }
        return $this->imageUploader->getMediaUrl($campaign->getImage());
    }

    public function getProductCollection(): Collection
    {
        $campaign = $this->getCampaign();
        if (!$campaign) {
            return $this->productCollectionBuilder->build(null);
        }
        return $this->productCollectionBuilder->build(
            $campaign->getConditionsSerialized(),
            $campaign->getSortBy(),
            $campaign->getSortOrder(),
            $campaign->getProductsCount()
        );
    }
}
