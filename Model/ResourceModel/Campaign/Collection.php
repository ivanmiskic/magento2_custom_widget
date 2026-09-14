<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Model\ResourceModel\Campaign;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\Campaign;
use Null\Blueprint\Model\ResourceModel\Campaign as CampaignResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = CampaignInterface::CAMPAIGN_ID;

    /**
     * @var bool
     */
    private bool $storeTableJoined = false;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(Campaign::class, CampaignResource::class);
    }

    /**
     * Limit to a store view. Store 0 (admin / all stores) always matches.
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter(int $storeId): self
    {
        $this->joinStoreTable();
        $this->getSelect()->where('store_table.store_id IN (?)', [0, $storeId]);
        $this->getSelect()->group('main_table.campaign_id');
        return $this;
    }

    /**
     * Active campaigns whose schedule window includes $now. Null dates are open-ended.
     *
     * @param \DateTimeInterface $now
     * @return $this
     */
    public function addVisibleFilter(\DateTimeInterface $now): self
    {
        $datetime = $now->format('Y-m-d H:i:s');
        $this->addFieldToFilter(CampaignInterface::STATUS, CampaignInterface::STATUS_ACTIVE);
        $this->getSelect()->where(
            '(main_table.start_at IS NULL OR main_table.start_at <= ?) AND (main_table.end_at IS NULL OR main_table.end_at >= ?)',
            $datetime
        );
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad(): self
    {
        parent::_afterLoad();
        $resource = $this->getResource();
        if (!$resource instanceof CampaignResource) {
            return $this;
        }
        foreach ($this->_items as $item) {
            $campaignId = (int)$item->getId();
            if ($campaignId) {
                $item->setData(CampaignInterface::STORE_IDS, $resource->lookupStoreIds($campaignId));
            }
        }
        return $this;
    }

    /**
     * @return void
     */
    private function joinStoreTable(): void
    {
        if ($this->storeTableJoined) {
            return;
        }
        $this->getSelect()->join(
            ['store_table' => $this->getTable('null_blueprint_campaign_store')],
            'main_table.campaign_id = store_table.campaign_id',
            []
        );
        $this->storeTableJoined = true;
    }
}
