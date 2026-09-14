<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Model\Campaign;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $loadedData = [];

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly ImageUploader $imageUploader,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getData(): array
    {
        if ($this->loadedData) {
            return $this->loadedData;
        }

        foreach ($this->collection->getItems() as $campaign) {
            $data = $campaign->getData();
            $campaignId = (int)$campaign->getId();
            $data[CampaignInterface::STORE_IDS] = $campaign->getStoreIds();
            $data[CampaignInterface::IMAGE] = $this->formatImage($campaign->getImage());
            $this->loadedData[$campaignId] = $data;
        }

        $persisted = $this->dataPersistor->get('null_blueprint_campaign');
        if (is_array($persisted) && $persisted) {
            $campaign = $this->collection->getNewEmptyItem();
            $campaign->setData($persisted);
            $this->loadedData[0] = $campaign->getData();
            $this->dataPersistor->clear('null_blueprint_campaign');
        }

        return $this->loadedData;
    }

    /**
     * @return array<int, array<string, string>>|null
     */
    private function formatImage(?string $image): ?array
    {
        if ($image === null || $image === '') {
            return null;
        }
        return [
            [
                'name' => $image,
                'url' => $this->imageUploader->getMediaUrl($image),
            ],
        ];
    }
}
