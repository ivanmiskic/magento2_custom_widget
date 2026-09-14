<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Api\Data;

/**
 * Campaign data contract.
 */
interface CampaignInterface
{
    public const CAMPAIGN_ID = 'campaign_id';
    public const IDENTIFIER = 'identifier';
    public const TITLE = 'title';
    public const DESCRIPTION = 'description';
    public const IMAGE = 'image';
    public const STATUS = 'status';
    public const START_AT = 'start_at';
    public const END_AT = 'end_at';
    public const CONDITIONS_SERIALIZED = 'conditions_serialized';
    public const SORT_BY = 'sort_by';
    public const SORT_ORDER = 'sort_order';
    public const PRODUCTS_COUNT = 'products_count';
    public const STORE_IDS = 'store_ids';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public const STATUS_DRAFT = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_EXPIRED = 2;

    public const CACHE_TAG = 'null_blueprint_campaign';
    public const WIDGET_CACHE_TAG = 'null_blueprint_campaign_widget';

    /**
     * @return int|null
     */
    public function getCampaignId(): ?int;

    /**
     * @param int $campaignId
     * @return $this
     */
    public function setCampaignId(int $campaignId): self;

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string;

    /**
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier(string $identifier): self;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @param string|null $image
     * @return $this
     */
    public function setImage(?string $image): self;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self;

    /**
     * @return string|null
     */
    public function getStartAt(): ?string;

    /**
     * @param string|null $startAt
     * @return $this
     */
    public function setStartAt(?string $startAt): self;

    /**
     * @return string|null
     */
    public function getEndAt(): ?string;

    /**
     * @param string|null $endAt
     * @return $this
     */
    public function setEndAt(?string $endAt): self;

    /**
     * @return string|null
     */
    public function getConditionsSerialized(): ?string;

    /**
     * @param string|null $conditions
     * @return $this
     */
    public function setConditionsSerialized(?string $conditions): self;

    /**
     * @return string
     */
    public function getSortBy(): string;

    /**
     * @param string $sortBy
     * @return $this
     */
    public function setSortBy(string $sortBy): self;

    /**
     * @return string
     */
    public function getSortOrder(): string;

    /**
     * @param string $sortOrder
     * @return $this
     */
    public function setSortOrder(string $sortOrder): self;

    /**
     * @return int
     */
    public function getProductsCount(): int;

    /**
     * @param int $productsCount
     * @return $this
     */
    public function setProductsCount(int $productsCount): self;

    /**
     * @return int[]
     */
    public function getStoreIds(): array;

    /**
     * @param int[] $storeIds
     * @return $this
     */
    public function setStoreIds(array $storeIds): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;
}
