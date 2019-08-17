<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Recommender helper
 */
namespace Techfactory\Recommendations\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\Stdlib\CookieManagerInterface;
use \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use \Magento\Framework\Session\SessionManagerInterface;

use \Techfactory\Recommendations\Helper\Data;
use \Techfactory\Recommendations\Helper\Http;
use \Techfactory\Recommendations\Helper\Utility;

class Recommender extends AbstractHelper
{
    /**
     * @var string
     */
    protected $prefix = 'mg';

    /**
     * @var \Techfactory\Recommendations\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Techfactory\Recommendations\Helper\Http
     */
    protected $_helperHttp;

    /**
     * @var \Techfactory\Recommendations\Helper\Utility
     */
    protected $_helperUtility;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Techfactory\Recommendations\Helper\Data $helperData
     * @param \Techfactory\Recommendations\Helper\Http $helperHttp
     * @param \Techfactory\Recommendations\Helper\Utility $helperUtility
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        Data $helperData,
        Http $helperHttp,
        Utility $helperUtility
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;

        $this->_helperData = $helperData;
        $this->_helperHttp = $helperHttp;
        $this->_helperUtility = $helperUtility;
    }

    public function sendingDemoData()
    {
        if( $this->getUserId() == 'Mahshad' ) {
            for($i=0; $i<10; $i++) {
                $uniqid = $this->_helperUtility->getUniqId();
                for($j=0; $j<15; $j++) {
                    $product_id = rand(1, 2047);

                    $objectManager = ObjectManager::getInstance();
                    $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
                    $product = $productRepository->getById($product_id);

                    $this->setIngest($product);
                }
            }
        }
    }

    /**
     * Get prefix string
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get user id
     *
     * @return string
     */
    public function getUserId()
    {
        $uniqid = $this->_helperUtility->getUniqId();
        $cookie = $this->_cookieManager->getCookie('__techfactoryRecomUser');

        if (empty($cookie)) {
            $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration(strtotime('+33 years'))
                ->setPath($this->_sessionManager->getCookiePath())
                ->setDomain($this->_sessionManager->getCookieDomain());

            $this->_cookieManager->setPublicCookie('__techfactoryRecomUser', $uniqid, $publicCookieMetadata);
        }

        return $this->_cookieManager->getCookie('__techfactoryRecomUser');
    }

    /**
     * Ingest an item
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function setIngest($product)
    {
        $product_id = $product->getId();
        $product_id = $this->prefix . $product_id;

        $terms = $this->_helperUtility->getTerms($product);

        $endpoint = 'set';

        $args = [
            'items' => [$product_id],
            'type' => 'ITEM_VIEWED',
            'user' => $this->getUserId(),
        ];

        if (empty($terms)) {
            $args['tags'] = $terms;
        }

        $method = 'POST';

        $this->_helperHttp->sendHttpRequest($endpoint, $args, $method);
    }

    /**
     * Get recommendations
     *
     * @param string $endpoint
     * @param array $args
     * @return array
     */
    public function getRecommendations($endpoint, $args)
    {
        if (empty($args)) {
            return;
        }

        $args['shuffle'] = "false";

        $recommendations = $this->_helperHttp->sendHttpRequest($endpoint, $args);

        return $this->_helperUtility->getIds($recommendations, $this->prefix);
    }
}
