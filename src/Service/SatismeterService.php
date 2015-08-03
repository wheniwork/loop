<?php
namespace Wheniwork\Feedback\Service;

use DateTime;

class SatismeterService
{
    private $key;
    private $product_id;

    public function __construct($key, $product_id)
    {
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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['AuthKey: ' . $this->key]);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data)->responses;
    }
}
