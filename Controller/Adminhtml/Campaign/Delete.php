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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Null\Blueprint\Api\CampaignRepositoryInterface;

class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Null_Blueprint::campaign_delete';

    public function __construct(
        Context $context,
        private readonly CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $campaignId = (int)$this->getRequest()->getParam('campaign_id');
        if (!$campaignId) {
            $this->messageManager->addErrorMessage(__('We cannot find a campaign to delete.'));
            return $resultRedirect->setPath('*/*/');
        }
        try {
            $this->campaignRepository->deleteById($campaignId);
            $this->messageManager->addSuccessMessage(__('You deleted the campaign.'));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('Something went wrong while deleting the campaign.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
