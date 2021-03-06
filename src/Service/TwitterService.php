<?php
namespace Wheniwork\Feedback\Service;

use TwitterAPIExchange;
use RuntimeException;

class TwitterService
{
    private $twitter;
    private $screen_name;

    const TWITTER_API_BASE_URL = 'https://api.twitter.com/1.1';

    public function __construct(TwitterAPIExchange $twitter, $screen_name)
    {
        $this->twitter = $twitter;
        $this->screen_name = $screen_name;
    }

    private function get($request, array $params = [])
    {
        $getfield = '';
        if (!empty($params)) {
            $getfield = '?' . http_build_query($params);
        }
        
        $result = json_decode(
            $this->twitter->setGetfield($getfield)
                ->buildOauth($request, 'GET')
                ->performRequest()
        );

        if (!empty($result->errors)) {
            $error = current($result->errors);
            throw new RuntimeException($error->message, $error->code);
        }

        return $result;
    }

    public function getTweetsSince($last_id)
    {
        return $this->get(self::TWITTER_API_BASE_URL . '/statuses/user_timeline.json', [
            'screen_name' => $this->screen_name,
            'since_id' => $last_id
        ]);
    }

    public function getTweet($id)
    {
        return $this->get(self::TWITTER_API_BASE_URL . '/statuses/show.json', [
            'id' => $id
        ]);
    }

    public function getTweetURL($tweet)
    {
        $screen_name = $tweet->user->screen_name;
        $id_str = $tweet->id_str;
        return "https://twitter.com/$screen_name/status/$id_str";
    }
}
