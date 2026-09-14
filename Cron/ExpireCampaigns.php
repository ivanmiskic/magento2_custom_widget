<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Cron;

use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Mail\CampaignExpiredSender;
use Null\Blueprint\Model\CampaignVisibility;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;

/**
 * PATTERN: Cron — daily expiry. Pair with etc/crontab.xml.
 */
class ExpireCampaigns
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignVisibility $campaignVisibility,
        private readonly CampaignExpiredSender $campaignExpiredSender
    ) {
    }

    public function execute(): void
    {
        $now = new \DateTimeImmutable('now');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CampaignInterface::STATUS, CampaignInterface::STATUS_ACTIVE);
        $collection->addFieldToFilter(CampaignInterface::END_AT, ['notnull' => true]);

        foreach ($collection as $campaign) {
            if (!$this->campaignVisibility->isDueForExpiry(
                $campaign->getStatus(),
                $campaign->getEndAt(),
                $now
            )) {
                continue;
            }
            $campaign->setStatus(CampaignInterface::STATUS_EXPIRED);
            $this->campaignRepository->save($campaign);
            $this->campaignExpiredSender->send($campaign);
        }
    }
}
