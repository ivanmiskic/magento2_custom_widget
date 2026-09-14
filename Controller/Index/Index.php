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
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Null\Blueprint\Model\Config;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $resultPageFactory,
        private readonly ForwardFactory $resultForwardFactory,
        private readonly Config $config
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->config->isEnabled() || !$this->config->isListEnabled()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('Campaigns'));
        return $page;
    }
}
