<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Utility helper
 */
namespace Techfactory\Recommendations\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

use \Techfactory\Recommendations\Helper\Data;

class Utility extends AbstractHelper
{
    /**
     * @var \Techfactory\Recommendations\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Techfactory\Recommendations\Helper\Data $helperData
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        Data $helperData
    ) {
        $this->_helperData = $helperData;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Generate random number for users
     *
     * @return string
     */
    public function getUniqId()
    {
        $user_cookie = hexdec(md5(microtime()));

        if (strpos($user_cookie, 'E') !== false) {
            $floatParts = explode('E', $user_cookie);

            $strNum = $floatParts[0];
            $num = (float) $floatParts[0];
            $pow = (int) $floatParts[1];

            $user_cookie =  $num * pow(10, strlen($strNum)-2);
        }

        return $user_cookie;
    }

    /**
     * Get product ids
     *
     * @return array
     */
    public function getIds($items, $prefix)
    {
        $product_ids = [];

        if (is_array($items)) {
            foreach ($items as $item) {
                $item_id = $item->item;

                if (strpos($item_id, $prefix) !== false) {
                    $product_ids[] = str_replace($prefix, '', $item_id);
                }
            }
        }

        return $product_ids;
    }

    /**
     * Get terms
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTerms($product)
    {
        $categories = $this->getCategory($product);
        $tags = $this->getTag($product);

        $terms = array_merge($categories, $tags);

        if ($terms) {
            $terms = array_map(
                function ($array) {
                    return implode(',', $array);
                },
                $terms
            );
        }

        $terms = array_values(array_filter($terms));

        return $terms;
    }

    /**
     * Filtered terms
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $category_ids
     * @param bool $filter_by_attributes
     * @return array
     */
    public function getFilterByTerms($product, $category_ids, $filter_by_attributes)
    {
        $terms = [];
        $categories = [];
        $tags = [];

        if (!empty($category_ids)) {
            if (!is_array($category_ids)) {
                $category_ids = explode(',', $category_ids);
            }

            $categories = $this->getCategoryByIds($category_ids);
        }

        if ($product && $filter_by_attributes) {
            $tags = $this->getTag($product);
        }

        $terms = array_merge($categories, $tags);

        if ($terms) {
            $terms = array_merge(...$terms);
        }

        $terms = array_filter($terms);

        return $terms;
    }

    /**
     * Get product category
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getCategory($product)
    {
        $category_ids = $product->getCategoryIds();

        return $this->getCategoryAndParentsByIds($category_ids);
    }

    /**
     * Get category by id
     *
     * @param array $category_ids
     * @return string
     */
    public function getCategoryByIds($category_ids)
    {
        $terms = [];

        $categories = $this->_categoryCollectionFactory->create()
                                ->addAttributeToSelect('name')
                                ->addAttributeToFilter('entity_id', $category_ids)
                                ->setPageSize(3)
                                ->setCurPage(1);

        if (empty($categories)) {
            return [];
        }

        foreach ($categories as $category) {
            $terms[] = preg_replace('/[^a-zA-Z0-9]+/', '_', $category->getName());
        }

        return [$terms];
    }

    /**
     * Get category and all its parents by id
     *
     * @param array $category_ids
     * @return string
     */
    public function getCategoryAndParentsByIds($category_ids)
    {
        $terms = [];
        $path_ids = [];

        $categories = $this->_categoryCollectionFactory->create()
                                ->addAttributeToSelect('path')
                                ->addAttributeToFilter('entity_id', ['in' => $category_ids])
                                ->setPageSize(3)
                                ->setCurPage(1);

        if (empty($categories)) {
            return [];
        }

        foreach ($categories as $category) {
            $new_path_ids = explode('/', $category->getPath());
            unset($new_path_ids[0]);

            $path_ids = array_merge($path_ids, $new_path_ids);
        }

        $unique_path_ids = array_unique($path_ids);

        $categories2 = $this->_categoryCollectionFactory->create()
                                ->addAttributeToSelect('name')
                                ->addAttributeToFilter('entity_id', ['in' => $unique_path_ids]);

        if (empty($categories2)) {
            return [];
        }

        foreach ($categories2 as $category) {
            $terms[] = preg_replace('/[^a-zA-Z0-9]+/', '_', $category->getName());
        }

        return [$terms];
    }

    /**
     * Get tags
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTag($product)
    {
        $terms = [];

        $attribute_tags = $this->_helperData->getAttributeTags();

        if (empty($attribute_tags)) {
            return [];
        }

        foreach ($attribute_tags as $attribute_tag) {
            if (!isset($attribute_tag)) {
                continue;
            }

            $tags = $product->getAttributeText($attribute_tag);

            if (!is_array($tags)) {
                $tags = [$tags];
            }

            foreach ($tags as &$tag) {
                $tag = preg_replace('/[^a-zA-Z0-9]+/', '_', $tag);
            }

            $terms[] = $tags;
        }

        return $terms;
    }
}
