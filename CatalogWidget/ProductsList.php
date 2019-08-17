<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Display an output for widgets
 */
namespace Techfactory\Recommendations\CatalogWidget;

use \Techfactory\Recommendations\Helper\Utility;
use \Techfactory\Recommendations\Helper\Recommender;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    /**
     * Default value for sort by
     */
    const DEFAULT_SORT_BY = 'id';

    /**
     * Default value for sort order
     */
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_registry;

    /**
     * @var \Techfactory\Recommendations\Helper\Utility
     */
    protected $_helperUtility;

    /**
     * @var \Techfactory\Recommendations\Helper\Recommender
     */
    protected $_helperRecommender;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Techfactory\Recommendations\Helper\Utility $helperUtility
     * @param \Techfactory\Recommendations\Helper\Recommender $helperRecommender
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        Utility $helperUtility,
        Recommender $helperRecommender,
        array $data = []
    ) {
        $this->_registry = $context->getRegistry();
        $this->_helperUtility = $helperUtility;
        $this->_helperRecommender = $helperRecommender;

        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $data
        );
    }

    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function createCollection()
    {
        $product_ids = $this->callRecommender();

        if (empty($product_ids)) {
            return;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => $product_ids]);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1));
        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        $collection->distinct(true);

        return $collection;
    }

    /**
     * Get products
     *
     * @return array
     */
    protected function callRecommender()
    {
        $endpoint = 'trend';
        $args = [
            'count' => $this->getData('products_count'),
            'shuffle' => "false",
        ];

        return $this->_helperRecommender->getRecommendations($endpoint, $args);
    }
}
