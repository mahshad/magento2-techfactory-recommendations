<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Http request helper
 */
namespace Techfactory\Recommendations\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Zend\Http\Client;
use \Zend\Http\Exception\RuntimeException;

use \Techfactory\Recommendations\Helper\Data;

class Http extends AbstractHelper
{
    /**
     * @var \Zend\Http\Client
     */
    protected $_zendClient;

    /**
     * @var \Techfactory\Recommendations\Helper\Data
     */
    protected $_helperData;

    /**
     * @param \Zend\Http\Client $zendClient
     * @param \Techfactory\Recommendations\Helper\Data $helperData
     */
    public function __construct(
        Client $zendClient,
        Data $helperData
    ) {
        $this->_zendClient = $zendClient;
        $this->_helperData = $helperData;
    }

    /**
     * Sending HTTP request
     * 
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @return array
     */
    public function sendHttpRequest($endpoint, $params, $method = 'GET')
    {
	$service_address = $this->_helperData->getServiceAddress();

        if (empty($service_address)) {
            return;
        }

        $args = '';
        $body = '';

        if ($method == 'GET') {
            if (!empty($params)) {
                $param = [];

                foreach ($params as $pkey => $pvalue) {
                    if (is_array($pvalue)) {
                        foreach ($pvalue as $value) {
                            $param[] = $pkey . '=' . $value;
                        }
                    } else {
                        $param[] = $pkey . '=' . $pvalue;
                    }
                }

                $param = implode('&', $param);
                $args = '?' . $param;
            }
        } else {
            $body = json_encode($params);
        }

        $uri = join('/', [$service_address, $endpoint, $args]);

        $headers = [
            'Content-Type' => 'application/json',
        ];

        return $this->sendRawHttpRequest($uri, $body, $method, $headers);
    }

    /**
     * Sending raw HTTP request
     * 
     * @param string $uri
     * @param array $body
     * @param string $method
     * @param array $headers
     * @return array
     */
    public function sendRawHttpRequest($uri, $body = null, $method = 'GET', $headers = null)
    {
        try {
            $this->_zendClient->reset();
            $this->_zendClient->setUri($uri);
            $this->_zendClient->setMethod($method);

            if (isset($body)) {
                $this->_zendClient->setRawBody($body);
            }

            if (isset($headers) && is_array($headers)) {
                $this->_zendClient->setHeaders($headers);
            }

            $this->_zendClient->send();

            $response = $this->_zendClient->getResponse();
            $content = $response->getBody();

            if (!empty($content)) {
                $json_decoded = json_decode($content);

                return (json_last_error() == JSON_ERROR_NONE) ? $json_decoded : $content;
            }

            return $response;
        } catch (RuntimeException $runtimeException) {
            return $runtimeException->getMessage();
        }
    }
}
