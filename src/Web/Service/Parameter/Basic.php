<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AstralWeb\LibUSPS\Web\Service\Parameter;

class Basic
{
    protected static $_initParams = [
        'get' => [
            'XML' => ''
        ]
    ];

    protected $domain = 'https://secure.shippingapis.com';

    protected $path = '';
    protected $timeoutSeconds = 60;
    protected $httpMethod = 'GET';
    protected $params = [];

    protected $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
    ];

    public function __construct()
    {
        $this->reset();
    }

    /**
     * @return self
     */
    public function reset()
    {
        $this->params = static::$_initParams;

        return $this;
    }

    /**
     * @param string $method
     * @param return array
     */
    public function toArrayByMethod(string $method)
    {
        $method = strtolower($method);

        return isset($this->params[$method]) ? $this->params[$method] : [];
    }

    /**
     * @return array
     */
    public function toMethodContentArray()
    {
        return $this->toArrayByMethod($this->httpMethod);
    }

    /**
     * @param \AstralWeb\LibUSPS\Web\Service\Client $client
     */
    public function setClient(\AstralWeb\LibUSPS\Web\Service\Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param string $domain
     * @return self
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @param string $method
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setMethodParam($method, $name, $value)
    {
        $method = strtolower($method);
        if ( ! isset($this->params[$method])) {
            $this->params[$method] = [];
        }

        $this->params[$method][$name] = $value;

        return $this;
    }

    /**
     * @param string $method
     * @param string $name
     * @return mixed
     */
    public function getMethodParam($method, $name)
    {
        if ( ! isset($this->params[$method][$name])) {

            return '';
        }

        return $this->params[$method][$name];
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->domain . $this->getPath();
    }

    public function getXML()
    {
        return $this->getMethodParam('get', 'XML');
    }

    public function getUserID()
    {
        if ($this->client) {

            return $this->client->getUserID();
        }

        return '';
    }

    public function setXML($xml)
    {
        return $this->setMethodParam('get', 'XML', $xml);
    }

    public function summarize()
    {
        return $this;
    }

    /**
     * @param object $carrier
     * @return object
     */
    public function applyToCarrier(object $carrier)
    {
        $this->summarize();

        switch (get_class($carrier)) {
            case '\GuzzleHttp\Client':

                break;
            case 'Magento\Framework\HTTP\Client\Curl':
                foreach ($this->headers as $key => $value) {
                    $carrier->addHeader($key, $value);
                }
                $carrier->setOption(CURLOPT_FOLLOWLOCATION, true);
                $carrier->setOption(CURLOPT_RETURNTRANSFER, true);
                $carrier->setOption(CURLOPT_SSL_CIPHER_LIST, 'ECDHE-RSA-AES128-GCM-SHA256');

                break;
            case 'Magento\Framework\HTTP\ZendClient':
                $carrier->setUri($this->domain . $this->getPath());
                switch ($this->httpMethod) {
                    case 'POST':
                        $postParams = $this->toArrayByMethod($this->httpMethod);
                        foreach ($postParams as $name => $value) {
                            $carrier->setParameterPost($name, $value);
                        }
                        break;
                }
                $carrier->setMethod($this->httpMethod);
                break;
            case 'Zend\Http\Request':

                //$httpHeaders = $this->objectManager->create('Zend\Http\Headers');
                //$httpHeaders->addHeaders($this->headers);
                //$httpHeaders->addHeaderLine('Content-Type', 'charset=UTF-8');
                //$httpHeaders->setCharset('UTF-8');

                // $carrier->setHeaders($httpHeaders);
                $carrier->setUri($this->domain . $this->getPath());
                $carrier->setMethod($this->httpMethod);

                $params = new \Zend\Stdlib\Parameters($this->toArrayByMethod($this->httpMethod));
                $carrier->setQuery($params);
                break;
            case 'Zend\Http\Client':
                $options = [
                   'adapter'   => 'Zend\Http\Client\Adapter\Curl',
                   'curloptions' => [
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CONNECTTIMEOUT => 30,
                        //CURLOPT_POSTFIELDS, http_build_query($this->toArrayByMethod($this->httpMethod)),
                        CURLOPT_SSL_CIPHER_LIST => 'ECDHE-RSA-AES128-GCM-SHA256'
                    ],
                   'maxredirects' => 0,
                   'timeout' => $this->timeoutSeconds
                 ];
                 $carrier->setOptions($options);
                 $carrier->setHeaders($this->headers);
                break;
            default:
                break;
        }

        return $carrier;
    }
}
