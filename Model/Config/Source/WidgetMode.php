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

class WidgetMode implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'campaign', 'label' => __('Use an existing campaign')],
            ['value' => 'custom', 'label' => __('Custom conditions')],
        ];
    }
}
