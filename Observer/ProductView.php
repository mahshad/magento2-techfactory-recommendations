<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * View product observer
 */
namespace Techfactory\Recommendations\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

use \Techfactory\Recommendations\Helper\Recommender;

class ProductView implements ObserverInterface
{
    /**
     * @var \Techfactory\Recommendations\Helper\Recommender
     */
    protected $_helperRecommender;

    /**
     * @param \Techfactory\Recommendations\Helper\Recommender $helperRecommender
     */
    public function __construct(Recommender $helperRecommender)
    {
        $this->_helperRecommender = $helperRecommender;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();

        $this->_helperRecommender->setIngest($product);

        $this->_helperRecommender->sendingDemoData();
    }
}
