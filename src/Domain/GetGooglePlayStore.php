<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\FeedbackRating;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;
use Wheniwork\Feedback\Service\GooglePlayStoreService;

class GetGooglePlayStore extends FeedbackGetDomain
{
    private $store;

    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database,
        RedisClient $redis,
        GooglePlayStoreService $store
    ) {
        parent::__construct($hipchat, $database, $redis);
        $this->store = $store;
    }

    protected function getRedisKey()
    {
        return 'google-play-last';
    }

    protected function getOutputKeyName()
    {
        return 'new_reviews';
    }

    protected function getRawFeedbacks()
    {
        return $this->store->getReviews($this->getRedisValue());
    }

    protected function getValueForRedis($feedbackItem)
    {
        return $feedbackItem['timestamp'];
    }

    protected function createFeedbackItem($rawFeedback)
    {
        return (new FeedbackItem)->withData([
            'body' => $rawFeedback['body'],
            'source' => 'the Google Play Store',
            'title' => $rawFeedback['title'],
            'rating' => new FeedbackRating($rawFeedback['rating'], 5),
            'tone' => $this->getTone($rawFeedback)
        ]);
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
