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

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\ResourceModel\Campaign as CampaignResource;

class Campaign extends AbstractModel implements CampaignInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'null_blueprint_campaign';

    /**
     * @var string
     */
    protected $_cacheTag = CampaignInterface::CACHE_TAG;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(CampaignResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(): array
    {
        $identities = [CampaignInterface::CACHE_TAG, CampaignInterface::WIDGET_CACHE_TAG];
        if ($this->getCampaignId()) {
            $identities[] = CampaignInterface::CACHE_TAG . '_' . $this->getCampaignId();
        }
        return $identities;
    }

    /**
     * @inheritDoc
     */
    public function getCampaignId(): ?int
    {
        $value = $this->getData(self::CAMPAIGN_ID);
        return $value !== null ? (int)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setCampaignId(int $campaignId): CampaignInterface
    {
        return $this->setData(self::CAMPAIGN_ID, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): ?string
    {
        $value = $this->getData(self::IDENTIFIER);
        return $value !== null ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $identifier): CampaignInterface
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        $value = $this->getData(self::TITLE);
        return $value !== null ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): CampaignInterface
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        $value = $this->getData(self::DESCRIPTION);
        return $value !== null ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(?string $description): CampaignInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function getImage(): ?string
    {
        $value = $this->getData(self::IMAGE);
        return $value !== null && $value !== '' ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setImage(?string $image): CampaignInterface
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(int $status): CampaignInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getStartAt(): ?string
    {
        $value = $this->getData(self::START_AT);
        return $value !== null && $value !== '' ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setStartAt(?string $startAt): CampaignInterface
    {
        return $this->setData(self::START_AT, $startAt);
    }

    /**
     * @inheritDoc
     */
    public function getEndAt(): ?string
    {
        $value = $this->getData(self::END_AT);
        return $value !== null && $value !== '' ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setEndAt(?string $endAt): CampaignInterface
    {
        return $this->setData(self::END_AT, $endAt);
    }

    /**
     * @inheritDoc
     */
    public function getConditionsSerialized(): ?string
    {
        $value = $this->getData(self::CONDITIONS_SERIALIZED);
        return $value !== null && $value !== '' ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setConditionsSerialized(?string $conditions): CampaignInterface
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditions);
    }

    /**
     * @inheritDoc
     */
    public function getSortBy(): string
    {
        $value = $this->getData(self::SORT_BY);
        return $value ? (string)$value : 'name';
    }

    /**
     * @inheritDoc
     */
    public function setSortBy(string $sortBy): CampaignInterface
    {
        return $this->setData(self::SORT_BY, $sortBy);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder(): string
    {
        $value = $this->getData(self::SORT_ORDER);
        return $value ? (string)$value : 'asc';
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder(string $sortOrder): CampaignInterface
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @inheritDoc
     */
    public function getProductsCount(): int
    {
        $value = $this->getData(self::PRODUCTS_COUNT);
        return $value ? (int)$value : 10;
    }

    /**
     * @inheritDoc
     */
    public function setProductsCount(int $productsCount): CampaignInterface
    {
        return $this->setData(self::PRODUCTS_COUNT, $productsCount);
    }

    /**
     * @inheritDoc
     */
    public function getStoreIds(): array
    {
        $value = $this->getData(self::STORE_IDS);
        if (!is_array($value)) {
            return [];
        }
        return array_map('intval', $value);
    }

    /**
     * @inheritDoc
     */
    public function setStoreIds(array $storeIds): CampaignInterface
    {
        return $this->setData(self::STORE_IDS, array_map('intval', $storeIds));
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);
        return $value !== null ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        $value = $this->getData(self::UPDATED_AT);
        return $value !== null ? (string)$value : null;
    }
}
