<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;

class CampaignList implements OptionSourceInterface
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('-- Please Select --')]];
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            CampaignInterface::STATUS,
            ['in' => [CampaignInterface::STATUS_DRAFT, CampaignInterface::STATUS_ACTIVE]]
        );
        $collection->setOrder(CampaignInterface::TITLE, 'ASC');
        foreach ($collection as $campaign) {
            $options[] = [
                'value' => $campaign->getCampaignId(),
                'label' => sprintf('%s (%s)', $campaign->getTitle(), $campaign->getIdentifier()),
            ];
        }
        return $options;
    }
}
