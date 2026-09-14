<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Null\Blueprint\Api\Data\CampaignInterface;

class Campaign extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init('null_blueprint_campaign', CampaignInterface::CAMPAIGN_ID);
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad(AbstractModel $object): self
    {
        if ($object->getId()) {
            $object->setData(CampaignInterface::STORE_IDS, $this->lookupStoreIds((int)$object->getId()));
        }
        return parent::_afterLoad($object);
    }

    /**
     * @inheritDoc
     */
    protected function _afterSave(AbstractModel $object): self
    {
        $this->saveStoreIds($object);
        return parent::_afterSave($object);
    }

    /**
     * @param int $campaignId
     * @return int[]
     */
    public function lookupStoreIds(int $campaignId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('null_blueprint_campaign_store'), 'store_id')
            ->where('campaign_id = ?', $campaignId);
        return array_map('intval', $connection->fetchCol($select));
    }

    /**
     * Persist store assignments. Store 0 means all stores.
     *
     * @param AbstractModel $object
     * @return void
     */
    private function saveStoreIds(AbstractModel $object): void
    {
        $campaignId = (int)$object->getId();
        $storeIds = $object->getData(CampaignInterface::STORE_IDS);
        if (!is_array($storeIds) || $storeIds === []) {
            $storeIds = [0];
        }
        $storeIds = array_unique(array_map('intval', $storeIds));

        $connection = $this->getConnection();
        $table = $this->getTable('null_blueprint_campaign_store');
        $connection->delete($table, ['campaign_id = ?' => $campaignId]);

        $insert = [];
        foreach ($storeIds as $storeId) {
            $insert[] = ['campaign_id' => $campaignId, 'store_id' => $storeId];
        }
        if ($insert) {
            $connection->insertMultiple($table, $insert);
        }
    }
}
