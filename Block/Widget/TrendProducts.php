<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Display an output for trending products widget
 */
namespace Techfactory\Recommendations\Block\Widget;

use \Techfactory\Recommendations\CatalogWidget\ProductsList;

class TrendProducts extends ProductsList
{
    /**
     * Get trending products
     *
     * @return array
     */
    protected function callRecommender()
    {
        $products_count = $this->getData('products_count');
        $filter_by_attributes = (int) $this->getData('filter_by_attributes');
        $category_ids = $this->getData('categories');

        $product = $this->_registry->registry('current_product');

        $endpoint = 'trend';
        $args = [
            'count' => $products_count,
        ];

        $terms = $this->_helperUtility->getFilterByTerms($product, $category_ids, $filter_by_attributes);
        
        if (!empty($terms)) {
            $args['tags'] = $terms;
        }

        return $this->_helperRecommender->getRecommendations($endpoint, $args);
    }
}
