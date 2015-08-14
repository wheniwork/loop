<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\Service\HipChatService;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\GooglePlayStoreService;

class GetGooglePlayStore extends FeedbackGetDomain
{
    private $store;

    public function __construct(
        HipChatService $hipchat,
        GithubService $github,
        RedisClient $redis,
        GooglePlayStoreService $store
    ) {
        parent::__construct($hipchat, $github, $redis);
        $this->store = $store;
    }

    protected function getRedisKey() {
        return "google-play-last";
    }

    protected function getSourceName() {
        return "the Google Play Store";
    }

    protected function getOutputKeyName()
    {
        return "new_reviews";
    }

    protected function getFeedbackItems()
    {
        return $this->store->getReviews($this->getRedisValue());
    }

    protected function getValueForRedis($feedbackItem)
    {
        return $feedbackItem['timestamp'];
    }

    protected function getFeedbackHTML($feedbackItem)
    {
        $title = $feedbackItem['title'];
        $rating = $feedbackItem['rating'];
        $body = $feedbackItem['body'];
        return "<strong>$title ($rating/5)</strong><br>$body";
    }

    protected function getTone($feedbackItem)
    {
        $score = $feedbackItem['rating'];

        $tone = self::NEUTRAL;
        if ($score >= 4) {
            $tone = self::POSITIVE;
        } else if ($score == 3) {
            $tone = self::PASSIVE;
        } else if ($score <= 2) {
            $tone = self::NEGATIVE;
        }
        return $tone;
    }
}
