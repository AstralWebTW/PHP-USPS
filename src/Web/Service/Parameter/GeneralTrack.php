<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AstralWeb\LibUSPS\Web\Service\Parameter;

class GeneralTrack extends \AstralWeb\LibUSPS\Web\Service\Parameter\Basic
{
    protected static $_initParams = [
        'get' => [
            'API' => 'TrackV2'
        ]
    ];

    protected $path = '/ShippingAPI.dll';
    protected $httpMethod = 'GET';


    protected $trackingNumbers = [];

    /**
     * @param string $number
     * @return self
     */
    public function addTrackingNumber(string $number)
    {
        $this->trackingNumbers[] = $number;

        return $this;
    }

    /**
     * @return self
     */
    public function reset()
    {
        $this->params = static::$_initParams + parent::$_initParams;
        $this->trackingNumbers = [];

        return $this;
    }

    public function summarize()
    {
        parent::summarize();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $trackRequest = $dom->createElement("TrackRequest");
        $trackRequest->setAttribute('USERID', $this->getUserID());

        foreach ($this->trackingNumbers as $trackingNumber) {
            $numberDom = $dom->createElement('TrackID');
            $numberDom->setAttribute('ID', $trackingNumber);
            $trackRequest->appendChild($numberDom);
        }

        $dom->appendChild($trackRequest);
        $xml = $dom->saveHtml();
        $this->setXML($xml);    

        return $this;
    }
}
