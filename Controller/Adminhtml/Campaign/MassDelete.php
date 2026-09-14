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
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;

class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'Null_Blueprint::campaign_delete';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection as $campaign) {
            $this->campaignRepository->delete($campaign);
            $deleted++;
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 campaign(s) have been deleted.', $deleted));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
