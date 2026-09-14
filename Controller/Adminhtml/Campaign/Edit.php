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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterfaceFactory;
use Null\Blueprint\Controller\Adminhtml\Campaign as CampaignAction;

class Edit extends CampaignAction
{
    public const ADMIN_RESOURCE = 'Null_Blueprint::campaign';

    public function __construct(
        Context $context,
        Registry $registry,
        CampaignRepositoryInterface $campaignRepository,
        CampaignInterfaceFactory $campaignFactory,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $registry, $campaignRepository, $campaignFactory);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $campaignId = (int)$this->getRequest()->getParam('campaign_id');
        $campaign = $this->campaignFactory->create();
        if ($campaignId) {
            try {
                $campaign = $this->campaignRepository->getById($campaignId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }
        $this->registry->register('null_blueprint_campaign', $campaign);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Null_Blueprint::campaign');
        $resultPage->getConfig()->getTitle()->prepend(
            $campaign->getCampaignId() ? $campaign->getTitle() : __('New Campaign')
        );
        return $resultPage;
    }
}
