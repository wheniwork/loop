<?php
namespace Wheniwork\Feedback\Service;

use DateTime;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;

class SatismeterService
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $product_id;

    public function __construct(HttpClient $httpClient, $key, $product_id)
    {
        $this->httpClient = $httpClient;
        $this->key = $key;
        $this->product_id = $product_id;
    }

    /**
     * @param int $after_time Unix timestamp after which to load responses
     * @return Request
     */
    public function getSatismeterRequest($after_time)
    {
        $endpoint = 'https://app.satismeter.com/api/responses';
        $params = http_build_query([
            'startDate' => date(DateTime::ISO8601, $after_time),
            'project' => $this->product_id
        ]);

        return new Request(
            'GET',
            "$endpoint?$params",
            [
                'AuthKey' => $this->key
            ]
        );
    }

    /**
     * Gets feedback responses from Satismeter as a PHP object.
     *
     * @param int $after_time Unix timestamp after which to load responses
     * @return object|mixed
     */
    public function getResponses($after_time)
    {
        $request = $this->getSatismeterRequest($after_time);
        $response = $this->httpClient->send($request);
        $data = $response->getBody();

        return json_decode($data)->responses;
    }
}
