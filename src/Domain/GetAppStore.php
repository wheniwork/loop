<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\Service\AppStoreService;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;

class GetAppStore extends FeedbackGetDomain
{
    private $appStore;

    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database,
        RedisClient $redis,
        AppStoreService $appStore
    ) {
        parent::__construct($hipchat, $database, $redis);
        $this->appStore = $appStore;
    }

    protected function getRedisKey()
    {
        return "app_store_last_id";
    }

    protected function getSourceName()
    {
        return "the iTunes App Store";
    }

    protected function getOutputKeyName()
    {
        return "new_reviews";
    }

    protected function getFeedbackItems()
    {
        return $this->appStore->getReviews($this->getRedisValue());
    }

    protected function getValueForRedis($feedbackItem)
    {
        return $feedbackItem['id'];
    }

    protected function getFeedbackHTML($feedbackItem)
    {
        $body = $feedbackItem['content'];
        $title = $feedbackItem['title'];
        $score = $feedbackItem['rating'];
        return "<strong>$title ($score/5)</strong><br>$body";
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
