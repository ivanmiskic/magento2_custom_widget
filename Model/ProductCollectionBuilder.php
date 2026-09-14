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

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Model\Rule;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Widget\Helper\Conditions;

/**
 * Builds a catalog product collection from Magento widget condition trees.
 */
class ProductCollectionBuilder
{
    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly Visibility $catalogProductVisibility,
        private readonly SqlBuilder $sqlBuilder,
        private readonly Rule $rule,
        private readonly Conditions $conditionsHelper
    ) {
    }

    /**
     * Empty or invalid conditions return an empty collection (never the whole catalog).
     *
     * @param string|null $conditionsSerialized
     * @param string $sortBy
     * @param string $sortOrder
     * @param int $pageSize
     * @return Collection
     */
    public function build(
        ?string $conditionsSerialized,
        string $sortBy = 'name',
        string $sortOrder = 'asc',
        int $pageSize = 10
    ): Collection {
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addStoreFilter();
        $collection->addAttributeToSelect('*');
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $collection->addTaxPercents();
        $collection->addUrlRewrite();

        $conditions = $this->decodeConditions($conditionsSerialized);
        if ($conditions === []) {
            $collection->addFieldToFilter('entity_id', ['eq' => 0]);
            $collection->setPageSize(0);
            return $collection;
        }

        $this->rule->loadPost(['conditions' => $conditions]);
        $combine = $this->rule->getConditions();
        $combine->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $combine);
        $collection->distinct(true);
        $collection->setOrder($sortBy, $sortOrder);
        $collection->setPageSize(max(0, $pageSize));
        $collection->setCurPage(1);
        return $collection;
    }

    /**
     * @return array<string, mixed>
     */
    public function decodeConditions(?string $conditionsSerialized): array
    {
        if ($conditionsSerialized === null || trim($conditionsSerialized) === '') {
            return [];
        }
        try {
            $decoded = $this->conditionsHelper->decode($conditionsSerialized);
        } catch (\Throwable $exception) {
            $decoded = json_decode($conditionsSerialized, true);
        }
        return is_array($decoded) ? $decoded : [];
    }
}
