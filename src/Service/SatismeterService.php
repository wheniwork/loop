<?php
namespace Wheniwork\Feedback\Service;

use DateTime;

class SatismeterService
{
    public static function getResponses($after_time)
    {
        $endpoint = "https://app.satismeter.com/api/responses";
        $params = http_build_query([
            'startDate' => date(DateTime::ISO8601, $after_time),
            'project' => $_ENV['SATISMETER_PRODUCT_ID']
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['AuthKey: ' . $_ENV['SATISMETER_KEY']]);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data)->responses;
    }
}
