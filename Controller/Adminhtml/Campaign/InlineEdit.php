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
use Magento\Framework\Controller\Result\JsonFactory;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;

class InlineEdit extends Action
{
    public const ADMIN_RESOURCE = 'Null_Blueprint::campaign_save';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!is_array($postItems) || !$postItems) {
            return $result->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach ($postItems as $campaignId => $data) {
            try {
                $campaign = $this->campaignRepository->getById((int)$campaignId);
                if (isset($data[CampaignInterface::STATUS])) {
                    $campaign->setStatus((int)$data[CampaignInterface::STATUS]);
                }
                if (isset($data[CampaignInterface::TITLE])) {
                    $campaign->setTitle((string)$data[CampaignInterface::TITLE]);
                }
                $this->campaignRepository->save($campaign);
            } catch (\Exception $exception) {
                $error = true;
                $messages[] = $exception->getMessage();
            }
        }

        return $result->setData(['messages' => $messages, 'error' => $error]);
    }
}
