<?php
namespace AstralWeb\LibUSPS;

class Example
{
    /**
     * @param string $trackingNumber
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    static public function isTrackingNumberDeilvered($trackingNumber, $userId)
    {
        $result = static::queryDeliveryOfTrackingNumbers([$trackingNumber], $userId);

        return $result[$trackingNumber];
    }

    /**
     * @param []string $trackingNumbers
     * @param int $userId
     * @return array  ["number1" => bool, "number2" => bool, ...]
     * @throws \Exception
     */
    static public function queryDeliveryOfTrackingNumbers(array $trackingNumbers, $userId)
    {
        $client = new \AstralWeb\LibUSPS\Web\Service\Client();
        $client->setUserID($userId);

        $parameter = new \AstralWeb\LibUSPS\Web\Service\Parameter\GeneralTrack();
        $parameter->setClient($client);
        foreach ($trackingNumbers as $trackingNumber) {
            $parameter->addTrackingNumber($trackingNumber);
        }

        $service = new \AstralWeb\LibUSPS\Web\Service();
        $formattedResponse = new \AstralWeb\LibUSPS\Web\Service\Response\GeneralTrack();
        $response = $service->generalTrack($parameter, $formattedResponse);
        if ($response->getErrorMsg()) {

            throw new \Exception($response->getErrorMsg());
        }

        $result = [];
        foreach ($trackingNumbers as $trackingNumber) {
            $result[$trackingNumber] = $response->isDeliveried($trackingNumber);
        }

        return $result;
    }
}
