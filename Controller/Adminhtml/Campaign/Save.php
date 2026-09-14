<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Controller\Adminhtml\Campaign;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Api\Data\CampaignInterfaceFactory;
use Null\Blueprint\Controller\Adminhtml\Campaign as CampaignAction;
use Null\Blueprint\Model\Campaign\ImageUploader;

class Save extends CampaignAction
{
    public const ADMIN_RESOURCE = 'Null_Blueprint::campaign_save';

    public function __construct(
        Context $context,
        Registry $registry,
        CampaignRepositoryInterface $campaignRepository,
        CampaignInterfaceFactory $campaignFactory,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly ImageUploader $imageUploader
    ) {
        parent::__construct($context, $registry, $campaignRepository, $campaignFactory);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $campaignId = isset($data[CampaignInterface::CAMPAIGN_ID])
            ? (int)$data[CampaignInterface::CAMPAIGN_ID]
            : 0;
        $campaign = $campaignId
            ? $this->campaignRepository->getById($campaignId)
            : $this->campaignFactory->create();

        try {
            $campaign->setIdentifier(strtolower(trim((string)($data[CampaignInterface::IDENTIFIER] ?? ''))));
            $campaign->setTitle(trim((string)($data[CampaignInterface::TITLE] ?? '')));
            $campaign->setDescription($data[CampaignInterface::DESCRIPTION] ?? null);
            $campaign->setStatus((int)($data[CampaignInterface::STATUS] ?? CampaignInterface::STATUS_DRAFT));
            $campaign->setStartAt($this->normalizeDate($data[CampaignInterface::START_AT] ?? null));
            $campaign->setEndAt($this->normalizeDate($data[CampaignInterface::END_AT] ?? null));
            $campaign->setConditionsSerialized($data[CampaignInterface::CONDITIONS_SERIALIZED] ?? null);
            $campaign->setSortBy((string)($data[CampaignInterface::SORT_BY] ?? 'name'));
            $campaign->setSortOrder((string)($data[CampaignInterface::SORT_ORDER] ?? 'asc'));
            $campaign->setProductsCount((int)($data[CampaignInterface::PRODUCTS_COUNT] ?? 10));
            $campaign->setStoreIds($this->normalizeStoreIds($data[CampaignInterface::STORE_IDS] ?? [0]));
            $campaign->setImage($this->imageUploader->resolveStoredName($data[CampaignInterface::IMAGE] ?? null));

            $this->campaignRepository->save($campaign);
            $this->messageManager->addSuccessMessage(__('You saved the campaign.'));
            $this->dataPersistor->clear('null_blueprint_campaign');

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['campaign_id' => $campaign->getCampaignId()]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('Something went wrong while saving the campaign.'));
        }

        $this->dataPersistor->set('null_blueprint_campaign', $data);
        return $resultRedirect->setPath('*/*/edit', ['campaign_id' => $campaignId ?: null]);
    }

    /**
     * @param mixed $value
     */
    private function normalizeDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (string)$value;
    }

    /**
     * @param mixed $storeIds
     * @return int[]
     */
    private function normalizeStoreIds($storeIds): array
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $storeIds = array_map('intval', $storeIds);
        return $storeIds === [] ? [0] : $storeIds;
    }
}
