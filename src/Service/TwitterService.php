<?php
namespace Wheniwork\Feedback\Service;

use TwitterAPIExchange;
use RuntimeException;

class TwitterService
{
    private $twitter;

    public function __construct(TwitterAPIExchange $twitter) {
        $this->twitter = $twitter;
    }

    public function get($request, $params = []) {
        $getfield = "";
        if (!empty($params)) {
            $getfield = "?" . http_build_query($params);
        }
        
        $result = json_decode(
            $this->twitter->setGetfield($getfield)
                ->buildOauth($request, 'GET')
                ->performRequest()
        );

        if (!empty($result->errors)) {
            $error = reset($result->errors);
            throw new RuntimeException($error->message, $error->code);
        }

        return $result;
    }
}
