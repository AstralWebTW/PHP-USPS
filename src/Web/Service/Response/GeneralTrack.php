<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AstralWeb\LibUSPS\Web\Service\Response;

class GeneralTrack extends \AstralWeb\LibUSPS\Web\Service\Response\Basic
{
    protected static $_decisionPatternMap = [
        'success' => '/(?i)was delivered/',
        'invalid' => '/(?i)Please verify your tracking number/',
        'sendout' => '/^(?i)Arrived|Accepted|Departed/'
    ];

    /**
     * @var []
     */
    protected $trackDeliveryResult = null;

    /**
     * @var []
     */
    protected $trackExistenceResult = null;

    /**
     * @var []
     */
    protected $trackErrorResult = null;

    /**
     * @var []
     */
    protected $trackSendoutResult = null;

    /**
     * @return void
     */
    protected function _parseTrackResult()
    {
        $xmlString = $this->getResponseBody();

        $successPattern = isset(static::$_decisionPatternMap['success']) ? static::$_decisionPatternMap['success'] : '/(?i)was delivered/';
        $invalidPattern = isset(static::$_decisionPatternMap['invalid']) ? static::$_decisionPatternMap['invalid'] : '/(?i)Please verify your tracking number/';
        $sendoutPattern = isset(static::$_decisionPatternMap['sendout']) ? static::$_decisionPatternMap['sendout'] : '/^(?i)Arrived|Accepted|Departed/';

        $domXml = new \DOMDocument();
        $domXml->loadXml($xmlString);

        $this->trackDeliveryResult = [];
        $this->trackExistenceResult = [];
        $this->trackErrorResult = [];
        $this->trackSendoutResult = [];

        $tracks = $domXml->getElementsByTagName('TrackInfo');

        foreach ($tracks as $track) {

            $trackingNo = $track->getAttribute('ID');

            $this->trackExistenceResult[$trackingNo] = true;
            $this->trackDeliveryResult[$trackingNo] = false;


            $error = $track->getElementsByTagName('Error');
            if ($error->length > 0) {

                $errNumberMsg = $errDescriptionMsg = "";

                $errNumber = $error[0]->getElementsByTagName('Number');
                if ($errNumber->length > 0) {
                    $errNumberMsg = $errNumber[0]->nodeValue;
                }
                $errDescription = $error[0]->getElementsByTagName('Description');
                if ($errDescription->length > 0) {
                    $errDescriptionMsg = $errDescription[0]->nodeValue;
                }

                $this->trackErrorResult[$trackingNo] = sprintf("[%s]%s",
                    $errNumberMsg,
                    $errDescriptionMsg
                );

                continue;
            }

            $summary = $track->getElementsByTagName('TrackSummary');

            if (preg_match($successPattern, $summary[0]->nodeValue)) {

                $this->trackDeliveryResult[$trackingNo] = $summary[0]->nodeValue;
                $this->sendoutPattern[$trackingNo] = true;
                continue;
            }
            if (preg_match($invalidPattern, $summary[0]->nodeValue)) {

                $this->trackExistenceResult[$trackingNo] = false;
                continue;
            }
            if (preg_match($sendoutPattern, $summary[0]->nodeValue)) {

                $this->trackSendoutResult[$trackingNo] = true;
                continue;
            }

            // check details
            /*
            $details = $track->getElementsByTagName('TrackDetail');
            foreach ($details as $detail) {
                if (preg_match($sendoutPattern, $detail->nodeValue)) {

                    $this->trackSendoutResult[$trackingNo] = true;
                    break;          
                }
            }*/
        }

    }

    /**
     * @return self
     */
    public function reset()
    {
            $this->trackDeliveryResult = null;
            $this->trackExistenceResult = null;
            $this->trackErrorResult = null;
            $this->trackSendoutResult = null;

        return parent::reset();
    }

    public function setResponse(string $k, $value)
    {
        if ($k == static::ATTR_RESPONSE_BODY) {

            $this->trackDeliveryResult = null;
            $this->trackExistenceResult = null;
            $this->trackErrorResult = null;
            $this->trackSendoutResult = null;
        }

        parent::setResponse($k, $value);

        return $this;
    }

    /**
     * @param string $trackingNo
     * @return string
     */
    public function getDeliveredMsg(string $trackingNo)
    {
        if ( ! $this->isDeliveried($trackingNo)) {

            return '';
        }

        return $this->trackDeliveryResult[$trackingNo];
    }

    /**
     * @param string $trackingNo
     * @return string
     */
    public function getTrackError(string $trackingNo)
    {
        if ($this->getResponseStatus() != 200
            or $this->getErrorNumber() != '') {

            return '';
        }

        if (is_null($this->trackErrorResult)) {

            $this->_parseTrackResult();
        }

        return isset($this->trackErrorResult[$trackingNo]) ? $this->trackErrorResult[$trackingNo] : '';
    }

    /**
     * @param string $trackingNo
     * @return bool
     */
    public function isExistence(string $trackingNo)
    {
        if ($this->getResponseStatus() != 200
            or $this->getErrorNumber() != '') {

            return true;
        }

        if (is_null($this->trackExistenceResult)) {

            $this->_parseTrackResult();
        }

        return isset($this->trackExistenceResult[$trackingNo]) ? $this->trackExistenceResult[$trackingNo] : true;
    }

    /**
     * @param string $trackingNo
     * @return bool
     */
    /*
    public function isSendout(string $trackingNo)
    {
        if ($this->getResponseStatus() != 200
            or $this->getErrorNumber() != '') {

            return false;
        }

        if (is_null($this->trackSendoutResult)) {
            $this->_parseTrackResult();
        }

        return isset($this->trackSendoutResult[$trackingNo]) ? $this->trackSendoutResult[$trackingNo] : false;
    }*/

    /**
     * @param string $trackingNo
     * @return bool
     */
    public function isDeliveried(string $trackingNo)
    {
        if ($this->getResponseStatus() != 200
            or $this->getErrorNumber() != '') {

            return false;
        }

        if (is_null($this->trackDeliveryResult)) {
            $this->_parseTrackResult();
        }

        return isset($this->trackDeliveryResult[$trackingNo]) ? (bool)$this->trackDeliveryResult[$trackingNo] : false;
    }
}
