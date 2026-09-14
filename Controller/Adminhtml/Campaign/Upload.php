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
use Null\Blueprint\Model\Campaign\ImageUploader;

class Upload extends Action
{
    public const ADMIN_RESOURCE = 'Null_Blueprint::campaign_save';

    public function __construct(
        Context $context,
        private readonly ImageUploader $imageUploader
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            return $result->setData($this->imageUploader->saveFileToTmpDir('image'));
        } catch (\Exception $exception) {
            return $result->setData(['error' => $exception->getMessage(), 'errorcode' => $exception->getCode()]);
        }
    }
}
