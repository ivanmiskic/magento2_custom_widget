<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Block\Widget;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Widget\Helper\Conditions;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\CampaignVisibility;
use Null\Blueprint\Model\Config;
use Null\Blueprint\Model\ProductCollectionBuilder;
use Psr\Log\LoggerInterface;

/**
 * PATTERN: Widget block — extends CatalogWidget ProductsList so condition SQL and pager stay Magento-native.
 */
class CampaignProducts extends ProductsList
{
    private ?CampaignInterface $resolvedCampaign = null;

    private bool $campaignResolved = false;

    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        HttpContext $httpContext,
        SqlBuilder $sqlBuilder,
        Rule $rule,
        Conditions $conditionsHelper,
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignVisibility $campaignVisibility,
        private readonly ProductCollectionBuilder $productCollectionBuilder,
        private readonly Config $moduleConfig,
        private readonly LoggerInterface $logger,
        array $data = [],
        ?Json $json = null,
        ?LayoutFactory $layoutFactory = null,
        ?EncoderInterface $urlEncoder = null,
        ?CategoryRepositoryInterface $categoryRepository = null,
        ?OptionsData $optionsData = null
    ) {
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $data,
            $json,
            $layoutFactory,
            $urlEncoder,
            $categoryRepository,
            $optionsData
        );
    }

    /**
     * @inheritDoc
     */
    public function toHtml()
    {
        if (!$this->moduleConfig->isEnabled()) {
            return '';
        }
        if ($this->getMode() === 'campaign') {
            $campaign = $this->getCampaign();
            if ($campaign === null || !$this->campaignVisibility->isVisible(
                $campaign->getStatus(),
                $campaign->getStartAt(),
                $campaign->getEndAt(),
                new \DateTimeImmutable('now')
            )) {
                return $this->fetchView($this->getTemplateFile('Null_Blueprint::widget/campaign/empty.phtml'));
            }
        }
        return parent::toHtml();
    }

    /**
     * @inheritDoc
     */
    public function createCollection(): Collection
    {
        $campaign = $this->getMode() === 'campaign' ? $this->getCampaign() : null;
        $conditions = $campaign
            ? $campaign->getConditionsSerialized()
            : ($this->getData('conditions_encoded') ?: $this->getData('conditions'));
        if (is_array($conditions)) {
            $conditions = $this->conditionsHelper->encode($conditions);
        }

        $sortBy = $campaign ? $campaign->getSortBy() : (string)($this->getData('collection_sort_by') ?: 'name');
        $sortOrder = $campaign ? $campaign->getSortOrder() : (string)($this->getData('collection_sort_order') ?: 'asc');
        $pageSize = $this->getPageSize();

        return $this->productCollectionBuilder->build(
            is_string($conditions) ? $conditions : null,
            $sortBy,
            $sortOrder,
            $pageSize
        );
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $override = parent::getTitle();
        if ($override) {
            return $override;
        }
        $campaign = $this->getCampaign();
        return $campaign ? $campaign->getTitle() : '';
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(): array
    {
        $identities = parent::getIdentities();
        $identities[] = CampaignInterface::WIDGET_CACHE_TAG;
        $campaign = $this->getCampaign();
        if ($campaign && $campaign->getCampaignId()) {
            $identities[] = CampaignInterface::CACHE_TAG . '_' . $campaign->getCampaignId();
        }
        return $identities;
    }

    public function getMode(): string
    {
        $mode = (string)$this->getData('mode');
        return $mode !== '' ? $mode : 'campaign';
    }

    public function getCampaign(): ?CampaignInterface
    {
        if ($this->campaignResolved) {
            return $this->resolvedCampaign;
        }
        $this->campaignResolved = true;
        $campaignId = (int)$this->getData('campaign_id');
        if (!$campaignId) {
            return null;
        }
        try {
            $this->resolvedCampaign = $this->campaignRepository->getById($campaignId);
        } catch (NoSuchEntityException $exception) {
            $this->logger->warning($exception->getMessage());
            $this->resolvedCampaign = null;
        }
        return $this->resolvedCampaign;
    }

    public function isEmpty(): bool
    {
        $collection = $this->getProductCollection();
        return !$collection || $collection->getSize() === 0;
    }
}
