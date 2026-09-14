<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Api\Data\CampaignInterfaceFactory;
use PHPUnit\Framework\TestCase;

/**
 * Run inside a Magento 2.4.8 install. Not executed by GitHub Actions.
 */
class CampaignRepositorySaveTest extends TestCase
{
    public function testSaveGetByIdentifierAndStoreFilter(): void
    {
        if (!class_exists(Bootstrap::class)) {
            $this->markTestSkipped('Magento integration bootstrap is not available.');
        }

        $objectManager = Bootstrap::getObjectManager();
        /** @var CampaignInterfaceFactory $factory */
        $factory = $objectManager->get(CampaignInterfaceFactory::class);
        /** @var CampaignRepositoryInterface $repository */
        $repository = $objectManager->get(CampaignRepositoryInterface::class);

        $identifier = 'itest-' . bin2hex(random_bytes(4));
        $campaign = $factory->create();
        $campaign->setIdentifier($identifier);
        $campaign->setTitle('Integration Test Campaign');
        $campaign->setStatus(CampaignInterface::STATUS_ACTIVE);
        $campaign->setStoreIds([0]);
        $campaign->setProductsCount(5);
        $saved = $repository->save($campaign);

        $this->assertNotNull($saved->getCampaignId());
        $loaded = $repository->getByIdentifier($identifier);
        $this->assertSame($saved->getCampaignId(), $loaded->getCampaignId());
        $this->assertSame([0], $loaded->getStoreIds());

        $repository->deleteById((int)$saved->getCampaignId());
    }
}
