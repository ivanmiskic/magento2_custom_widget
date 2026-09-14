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

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Api\Data\CampaignInterfaceFactory;
use Null\Blueprint\Api\Data\CampaignSearchResultsInterface;
use Null\Blueprint\Api\Data\CampaignSearchResultsInterfaceFactory;
use Null\Blueprint\Model\Campaign\IdentifierValidator;
use Null\Blueprint\Model\ResourceModel\Campaign as CampaignResource;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;
use Psr\Log\LoggerInterface;

class CampaignRepository implements CampaignRepositoryInterface
{
    public function __construct(
        private readonly CampaignResource $resource,
        private readonly CampaignInterfaceFactory $campaignFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly CampaignSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly IdentifierValidator $identifierValidator,
        private readonly StoreManagerInterface $storeManager,
        private readonly EventManagerInterface $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getById(int $campaignId): CampaignInterface
    {
        $campaign = $this->campaignFactory->create();
        $this->resource->load($campaign, $campaignId);
        if (!$campaign->getCampaignId()) {
            throw new NoSuchEntityException(__('Campaign with id "%1" does not exist.', $campaignId));
        }
        return $campaign;
    }

    /**
     * @inheritDoc
     */
    public function getByIdentifier(string $identifier): CampaignInterface
    {
        $campaign = $this->campaignFactory->create();
        $this->resource->load($campaign, $identifier, CampaignInterface::IDENTIFIER);
        if (!$campaign->getCampaignId()) {
            throw new NoSuchEntityException(__('Campaign with identifier "%1" does not exist.', $identifier));
        }
        return $campaign;
    }

    /**
     * REST list: active campaigns inside the schedule window for the current store.
     *
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): CampaignSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter((int)$this->storeManager->getStore()->getId());
        $collection->addVisibleFilter(new \DateTimeImmutable('now'));
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function save(CampaignInterface $campaign): CampaignInterface
    {
        $identifier = (string)$campaign->getIdentifier();
        if (!$this->identifierValidator->isValid($identifier)) {
            throw new CouldNotSaveException(
                __('Campaign identifier must be a lowercase URL key (letters, digits, hyphens), max 64 characters.')
            );
        }
        if (!(string)$campaign->getTitle()) {
            throw new CouldNotSaveException(__('Campaign title is required.'));
        }

        try {
            $this->assertIdentifierUnique($campaign);
            $this->resource->save($campaign);
        } catch (CouldNotSaveException $exception) {
            throw $exception;
        } catch (AlreadyExistsException $exception) {
            throw new CouldNotSaveException(
                __('Campaign identifier "%1" already exists.', $identifier),
                $exception
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            throw new CouldNotSaveException(__('Could not save the campaign.'), $exception);
        }

        $this->eventManager->dispatch('null_blueprint_campaign_save_after', ['campaign' => $campaign]);
        return $this->getById((int)$campaign->getCampaignId());
    }

    /**
     * @inheritDoc
     */
    public function delete(CampaignInterface $campaign): bool
    {
        try {
            $this->resource->delete($campaign);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            throw new CouldNotDeleteException(__('Could not delete the campaign.'), $exception);
        }
        $this->eventManager->dispatch('null_blueprint_campaign_save_after', ['campaign' => $campaign]);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $campaignId): bool
    {
        return $this->delete($this->getById($campaignId));
    }

    /**
     * @throws CouldNotSaveException
     */
    private function assertIdentifierUnique(CampaignInterface $campaign): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CampaignInterface::IDENTIFIER, $campaign->getIdentifier());
        if ($campaign->getCampaignId()) {
            $collection->addFieldToFilter(
                CampaignInterface::CAMPAIGN_ID,
                ['neq' => $campaign->getCampaignId()]
            );
        }
        if ($collection->getSize() > 0) {
            throw new CouldNotSaveException(
                __('Campaign identifier "%1" already exists.', $campaign->getIdentifier())
            );
        }
    }
}
