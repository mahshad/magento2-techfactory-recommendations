<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Configuration helper
 */
namespace Techfactory\Recommendations\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Config\Storage\WriterInterface;
use \Magento\Framework\App\Cache\TypeListInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

class Data extends AbstractHelper
{
    /**
     * Configuration paths
     */
    const XML_PATH_SERVICE_ADDRESS = 'account_settings/general/service_address';
    const XML_PATH_ATTRIBUTE_TAGS = 'recommendations/product_attributes/attribute_tag_';

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $_configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TypeListInterface $cacheTypeList
    ) {
        $this->_configWriter = $configWriter;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_storeScope = ScopeInterface::SCOPE_STORE;
    }

    /**
     * Get account number
     *
     * @return string
     */
    public function getServiceAddress()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_SERVICE_ADDRESS,
            $this->_storeScope,
            $this->_storeManager->getStore()->getStoreId()
        );
    }

    /**
     * Get attribute tags
     *
     * @return array
     */
    public function getAttributeTags()
    {
        $result = [];

        for ($number = 1; $number <= 3; $number++) {
            $result[] = $this->_scopeConfig->getValue(
                self::XML_PATH_ATTRIBUTE_TAGS . $number,
                $this->_storeScope,
                $this->_storeManager->getStore()->getStoreId()
            );
        }

        return $result;
    }

    /**
     * Reset config cache
     *
     * @return void
     */
    public function resetConfigCache()
    {
        $this->_cacheTypeList->cleanType('config');
    }
}
