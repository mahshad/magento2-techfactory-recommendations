<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Attribute tags model
 */
namespace Techfactory\Recommendations\Model;

use \Magento\Framework\Option\ArrayInterface;
use \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
 
class AttributeTags implements ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $attributeCollection = $this->_attributeCollectionFactory->create()
                                                ->addIsFilterableFilter()
                                                ->removePriceFilter();

        $result[] = ['value' => '', 'label' => 'Choose an attribute tag...'];

        foreach ($attributeCollection as $attribute) {
            $attribute = $attribute->getData();
            $result[] = ['value' => $attribute['attribute_code'], 'label' => $attribute['frontend_label']];
        }

        return $result;
    }
}
