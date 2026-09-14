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

use Magento\Catalog\Model\ImageUploader as CatalogImageUploader;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImageUploader
{
    public function __construct(
        private readonly CatalogImageUploader $imageUploader,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function saveFileToTmpDir(string $fileId): array
    {
        return $this->imageUploader->saveFileToTmpDir($fileId);
    }

    public function moveFileFromTmp(string $imageName): string
    {
        return $this->imageUploader->moveFileFromTmp($imageName, true);
    }

    public function getBaseTmpPath(): string
    {
        return $this->imageUploader->getBaseTmpPath();
    }

    public function getBasePath(): string
    {
        return $this->imageUploader->getBasePath();
    }

    public function getMediaUrl(string $fileName): string
    {
        $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return rtrim($base, '/') . '/' . trim($this->getBasePath(), '/') . '/' . ltrim($fileName, '/');
    }

    /**
     * Normalize UI uploader payload or a plain filename into a stored relative name.
     *
     * @param mixed $image
     */
    public function resolveStoredName($image): ?string
    {
        if (is_array($image)) {
            $first = $image[0] ?? $image;
            if (!empty($first['name']) && empty($first['error'])) {
                $name = (string)$first['name'];
                if (!empty($first['tmp_name']) || !empty($first['file'])) {
                    return $this->moveFileFromTmp($name);
                }
                return $name;
            }
            return null;
        }
        if (is_string($image) && $image !== '') {
            return $image;
        }
        return null;
    }
}
