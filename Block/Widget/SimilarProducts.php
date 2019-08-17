<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Display an output for similar products widget
 */
namespace Techfactory\Recommendations\Block\Widget;

use \Techfactory\Recommendations\CatalogWidget\ProductsList;

class SimilarProducts extends ProductsList
{
    /**
     * Get similar products
     *
     * @return array
     */
    protected function callRecommender()
    {
        $products_count = $this->getData('products_count');
        $filter_by_attributes = (int) $this->getData('filter_by_attributes');

        $product = $this->_registry->registry('current_product');

        if (!$product) {
            return;
        }

        $current_product_id = $product->getId();
        $prefix = $this->_helperRecommender->getPrefix();

        $endpoint = 'similarity';
        $args = [
            'count' => $products_count,
            'items' => $prefix . $current_product_id,
        ];

        $category_ids = $product->getCategoryIds();
        $terms = $this->_helperUtility->getFilterByTerms($product, $category_ids, $filter_by_attributes);
        
        if (!empty($terms)) {
            $args['tags'] = $terms;
        }

        return $this->_helperRecommender->getRecommendations($endpoint, $args);
    }
}
