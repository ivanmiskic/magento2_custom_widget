<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Api\Data\CampaignSearchResultsInterface;

/**
 * PATTERN: Repository — persist and load Campaign through this interface, never from controllers.
 */
interface CampaignRepositoryInterface
{
    /**
     * @param int $campaignId
     * @return \Null\Blueprint\Api\Data\CampaignInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $campaignId): CampaignInterface;

    /**
     * @param string $identifier
     * @return \Null\Blueprint\Api\Data\CampaignInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByIdentifier(string $identifier): CampaignInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Null\Blueprint\Api\Data\CampaignSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): CampaignSearchResultsInterface;

    /**
     * @param \Null\Blueprint\Api\Data\CampaignInterface $campaign
     * @return \Null\Blueprint\Api\Data\CampaignInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(CampaignInterface $campaign): CampaignInterface;

    /**
     * @param \Null\Blueprint\Api\Data\CampaignInterface $campaign
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(CampaignInterface $campaign): bool;

    /**
     * @param int $campaignId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $campaignId): bool;
}
