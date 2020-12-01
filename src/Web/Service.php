<?php
namespace AstralWeb\LibUSPS\Web;

class Service
{
    /**
     * @param \AstralWeb\LibUSPS\Web\Service\Parameter\GeneralTrack $paramter
     * @param \AstralWeb\LibUSPS\Web\Service\Response\GeneralTrack $formattedResponse
     * @return \AstralWeb\LibUSPS\Web\Service\Response\GeneralTrack $formattedResponse
     * @throws Exception
     */
    public function generalTrack(
        \AstralWeb\LibUSPS\Web\Service\Parameter\GeneralTrack $parameter,
        \AstralWeb\LibUSPS\Web\Service\Response\GeneralTrack $formattedResponse
    ) {
        $httpClient = new \GuzzleHttp\Client();

        $parameter->applyToCarrier($httpClient);

        $url = sprintf("%s?%s",
            $parameter->getUrl(),
            http_build_query($parameter->toMethodContentArray())
        );
        $response = $httpClient->request('GET', $url);

        $formattedResponse
            ->setResponse($formattedResponse::ATTR_RESPONSE_BODY, $response->getBody())
            ->setResponse($formattedResponse::ATTR_RESPONSE_STATUS, $response->getStatusCode())
            ->setResponse($formattedResponse::ATTR_RESPONSE_HEADERS, $response->getHeaders());

        return $formattedResponse;
    }
}
