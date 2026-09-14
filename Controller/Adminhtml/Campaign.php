<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterfaceFactory;

abstract class Campaign extends Action
{
    public const ADMIN_RESOURCE = 'Null_Blueprint::campaign';

    public function __construct(
        Context $context,
        protected readonly Registry $registry,
        protected readonly CampaignRepositoryInterface $campaignRepository,
        protected readonly CampaignInterfaceFactory $campaignFactory
    ) {
        parent::__construct($context);
    }
}
