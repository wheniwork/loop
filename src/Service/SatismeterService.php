<?php
namespace Wheniwork\Feedback\Service;

use DateTime;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;

class SatismeterService
{
    private $httpClient;
    private $key;
    private $product_id;

    public function __construct(HttpClient $httpClient, $key, $product_id)
    {
        $this->httpClient = $httpClient;
        $this->key = $key;
        $this->product_id = $product_id;
    }

    public function getResponses($after_time)
    {
        $endpoint = "https://app.satismeter.com/api/responses";
        $params = http_build_query([
            'startDate' => date(DateTime::ISO8601, $after_time),
            'project' => $this->product_id
        ]);

        $request = new Request(
            "GET",
            "$endpoint?$params",
            [
                "AuthKey" => $this->key
            ]
        );
        $response = $this->httpClient->send($request);
        $data = $response->getBody();

        return json_decode($data)->responses;
    }
}
