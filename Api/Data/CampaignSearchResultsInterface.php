<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Campaign search results.
 */
interface CampaignSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Null\Blueprint\Api\Data\CampaignInterface[]
     */
    public function getItems();

    /**
     * @param \Null\Blueprint\Api\Data\CampaignInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
