<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Null\Blueprint\Model\CampaignBadgeResolver;

/**
 * PATTERN: Plugin — afterGetProductPriceHtml only. Do not widen this plugin.
 */
class AbstractProductPlugin
{
    public function __construct(
        private readonly CampaignBadgeResolver $campaignBadgeResolver,
        private readonly Escaper $escaper
    ) {
    }

    /**
     * @param AbstractProduct $subject
     * @param string $result
     * @param Product $product
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductPriceHtml(
        AbstractProduct $subject,
        string $result,
        Product $product
    ): string {
        $label = $this->campaignBadgeResolver->getBadgeLabel((int)$product->getId());
        if ($label === null || $label === '') {
            return $result;
        }
        $badge = sprintf(
            '<span class="blueprint-campaign-badge">%s</span>',
            $this->escaper->escapeHtml($label)
        );
        return $badge . $result;
    }
}
