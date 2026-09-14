<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Model\CampaignVisibility;
use Null\Blueprint\Model\Config;

class View implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly PageFactory $resultPageFactory,
        private readonly ForwardFactory $resultForwardFactory,
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignVisibility $campaignVisibility,
        private readonly Config $config
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $campaignId = (int)$this->request->getParam('id');
        try {
            $campaign = $this->campaignRepository->getById($campaignId);
        } catch (NoSuchEntityException $exception) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        if (!$this->campaignVisibility->isVisible(
            $campaign->getStatus(),
            $campaign->getStartAt(),
            $campaign->getEndAt(),
            new \DateTimeImmutable('now')
        )) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set($campaign->getTitle());
        return $page;
    }
}
