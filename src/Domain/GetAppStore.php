<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\FeedbackRating;
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

    protected function getOutputKeyName()
    {
        return "new_reviews";
    }

    protected function getRawFeedbacks()
    {
        return $this->appStore->getReviews($this->getRedisValue());
    }

    protected function createFeedbackItem($rawFeedback)
    {
        return (new FeedbackItem)->withData([
            'body' => $rawFeedback['content'],
            'title' => $rawFeedback['title'],
            'source' => "the iTunes App Store",
            'rating' => new FeedbackRating($rawFeedback['rating'], 5),
            'tone' => $this->getTone($rawFeedback)
        ]);
    }

    protected function getValueForRedis($feedbackItem)
    {
        return $feedbackItem['id'];
    }

    protected function getTone($rawFeedback)
    {
        $score = $rawFeedback['rating'];

        $tone = FeedbackItem::NEUTRAL;
        if ($score >= 4) {
            $tone = FeedbackItem::POSITIVE;
        } elseif ($score == 3) {
            $tone = FeedbackItem::PASSIVE;
        } elseif ($score <= 2) {
            $tone = FeedbackItem::NEGATIVE;
        }
        return $tone;
    }
}
