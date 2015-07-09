<?php
namespace Wheniwork\Feedback\Service;

use TwitterAPIExchange;
use RuntimeException;

class TwitterService
{
    private static function getTwitter() {
        $settings = [
            'oauth_access_token' => $_ENV['TWITTER_OAUTH_TOKEN'],
            'oauth_access_token_secret' => $_ENV['TWITTER_OAUTH_TOKEN_SECRET'],
            'consumer_key' => $_ENV['TWITTER_CONSUMER_TOKEN'],
            'consumer_secret' => $_ENV['TWITTER_CONSUMER_TOKEN_SECRET']
        ];
        return new TwitterAPIExchange($settings);
    }

    public static function get($request, $params = []) {
        $getfield = "";
        if (!empty($params)) {
            $getfield = "?" . http_build_query($params);
        }
        
        $twitter = self::getTwitter();
        $result = json_decode(
            $twitter->setGetfield($getfield)
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
