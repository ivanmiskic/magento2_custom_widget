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

class Status implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => CampaignInterface::STATUS_DRAFT, 'label' => __('Draft')],
            ['value' => CampaignInterface::STATUS_ACTIVE, 'label' => __('Active')],
            ['value' => CampaignInterface::STATUS_EXPIRED, 'label' => __('Expired')],
        ];
    }
}
