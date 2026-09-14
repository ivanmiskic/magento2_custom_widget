<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Test\Unit\Model;

use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\CampaignVisibility;
use PHPUnit\Framework\TestCase;

class CampaignVisibilityTest extends TestCase
{
    private CampaignVisibility $visibility;

    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->visibility = new CampaignVisibility();
        $this->now = new \DateTimeImmutable('2026-06-15 12:00:00');
    }

    public function testDraftIsNotVisible(): void
    {
        $this->assertFalse($this->visibility->isVisible(
            CampaignInterface::STATUS_DRAFT,
            null,
            null,
            $this->now
        ));
    }

    public function testActiveOpenEndedIsVisible(): void
    {
        $this->assertTrue($this->visibility->isVisible(
            CampaignInterface::STATUS_ACTIVE,
            null,
            null,
            $this->now
        ));
    }

    public function testFutureStartIsHidden(): void
    {
        $this->assertFalse($this->visibility->isVisible(
            CampaignInterface::STATUS_ACTIVE,
            '2026-07-01 00:00:00',
            null,
            $this->now
        ));
    }

    public function testPastEndIsHidden(): void
    {
        $this->assertFalse($this->visibility->isVisible(
            CampaignInterface::STATUS_ACTIVE,
            null,
            '2026-06-01 00:00:00',
            $this->now
        ));
    }

    public function testActiveWindowIsVisible(): void
    {
        $this->assertTrue($this->visibility->isVisible(
            CampaignInterface::STATUS_ACTIVE,
            '2026-06-01 00:00:00',
            '2026-06-30 23:59:59',
            $this->now
        ));
    }

    public function testDueForExpiryRequiresPastEndAndActive(): void
    {
        $this->assertTrue($this->visibility->isDueForExpiry(
            CampaignInterface::STATUS_ACTIVE,
            '2026-06-01 00:00:00',
            $this->now
        ));
        $this->assertFalse($this->visibility->isDueForExpiry(
            CampaignInterface::STATUS_ACTIVE,
            null,
            $this->now
        ));
        $this->assertFalse($this->visibility->isDueForExpiry(
            CampaignInterface::STATUS_DRAFT,
            '2026-06-01 00:00:00',
            $this->now
        ));
    }
}
