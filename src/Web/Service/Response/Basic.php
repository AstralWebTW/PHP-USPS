<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AstralWeb\LibUSPS\Web\Service\Response;

class Basic
{
    const ATTR_RESPONSE_BODY = 'body';
    const ATTR_RESPONSE_STATUS = 'http_status';
    const ATTR_RESPONSE_HEADERS = 'http_headers';

    protected static $_initResponse = [
        self::ATTR_RESPONSE_STATUS => null,
        self::ATTR_RESPONSE_BODY => null,
        self::ATTR_RESPONSE_HEADERS => [],
        'error_number' => null,
        'error_msg' => null
    ];

    protected $response = [];

    public function __construct()
    {
        $this->reset();
    }

    protected function _parseResult()
    {
        if ( ! is_null($this->response['error_number'])) {

            return;
        }


        $this->response['error_number'] = '';
        $this->response['error_msg'] = '';

        $xmlString = $this->getResponseBody();

        $domXml = new \DOMDocument();
        $domXml->loadXml($xmlString);

        $error = $domXml->getElementsByTagName('Error');
        if ($error->length == 0) {

            return;
        }

        $number = $error[0]->getElementsByTagName('Number');
        if ($number->length > 0) {
            $this->response['error_number'] = $number[0]->nodeValue;
        }

        $msg = $error[0]->getElementsByTagName('Description');
        if ($msg->length > 0) {
            $this->response['error_msg'] = $msg[0]->nodeValue;
        }
    }

    /**
     * @return self
     */
    public function reset()
    {
        foreach (static::$_initResponse as $k => $v) {

            $this->setResponse($k, $v);
        }

        return $this;
    }

    public function setResponse(string $k, $value)
    {
        if ($k == static::ATTR_RESPONSE_BODY) {

            $this->response['error_number'] = null;
            $this->response['error_msg'] = null;
        }

        $this->response[$k] = $value;

        return $this;
    }

    public function getResponseStatus()
    {
        return $this->response[static::ATTR_RESPONSE_STATUS];
    }

    public function getResponssHeaders()
    {
        return $this->response[static::ATTR_RESPONSE_HEADERS];
    }

    public function getResponseBody()
    {
        return $this->response[static::ATTR_RESPONSE_BODY];
    }

    public function getErrorNumber()
    {
        $this->_parseResult();

        return $this->response['error_number'];
    }

    public function getErrorMsg()
    {
        $this->_parseResult();

        return $this->response['error_msg'];
    }
}
