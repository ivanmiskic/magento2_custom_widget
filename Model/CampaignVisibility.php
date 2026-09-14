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

use Null\Blueprint\Api\Data\CampaignInterface;

/**
 * Pure schedule/status rules. Safe to unit-test without Magento.
 */
class CampaignVisibility
{
    /**
     * Storefront, widget, and REST list visibility.
     */
    public function isVisible(
        int $status,
        ?string $startAt,
        ?string $endAt,
        \DateTimeImmutable $now
    ): bool {
        if ($status !== CampaignInterface::STATUS_ACTIVE) {
            return false;
        }
        if ($startAt !== null && $startAt !== '' && new \DateTimeImmutable($startAt) > $now) {
            return false;
        }
        if ($endAt !== null && $endAt !== '' && new \DateTimeImmutable($endAt) < $now) {
            return false;
        }
        return true;
    }

    /**
     * Cron should expire an active campaign whose end_at is in the past.
     */
    public function isDueForExpiry(
        int $status,
        ?string $endAt,
        \DateTimeImmutable $now
    ): bool {
        if ($status !== CampaignInterface::STATUS_ACTIVE) {
            return false;
        }
        if ($endAt === null || $endAt === '') {
            return false;
        }
        return new \DateTimeImmutable($endAt) < $now;
    }
}
