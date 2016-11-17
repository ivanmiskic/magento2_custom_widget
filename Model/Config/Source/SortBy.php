<?php
namespace Inchoo\CatalogWidget\Model\Config\Source;

class SortBy implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'name', 'label' => __('Product Name')],
            ['value' => 'price', 'label' => __('Price')]
        ];
    }
}
