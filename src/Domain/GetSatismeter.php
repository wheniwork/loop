<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\FeedbackRating;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;
use Wheniwork\Feedback\Service\SatismeterService;

class GetSatismeter extends FeedbackGetDomain
{
    private $satismeter;
    
    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database,
        RedisClient $redis,
        SatismeterService $satismeter
    ) {
        parent::__construct($hipchat, $database, $redis);
        $this->satismeter = $satismeter;
    }

    protected function getRedisKey()
    {
        return "satismeter_last_time";
    }

    protected function getOutputKeyName()
    {
        return "new_responses";
    }

    protected function getRawFeedbacks()
    {
        $rawFeedbacks = [];

        $responses = $this->satismeter->getResponses($this->getRedisValue());
        foreach ($responses as $response) {
            if (empty($response->feedback)) {
                continue;
            }

            $rawFeedbacks[] = $response;
        }

        return $rawFeedbacks;
    }

    protected function createFeedbackItem($rawFeedback)
    {
        return (new FeedbackItem)->withData([
            'body' => $rawFeedback->feedback,
            'source' => "Satismeter",
            'rating' => new FeedbackRating($rawFeedback->rating, 10),
            'sender' => $rawFeedback->user->email,
            'tone' => $this->getTone($rawFeedback)
        ]);
    }

    protected function initRedis()
    {
        $startOfDay = strtotime("midnight");
        $this->setRedisValue($startOfDay);
    }

    protected function getValueForRedis($response)
    {
        return strtotime($response->created) + 1;
    }

    protected function getTone($rawFeedback)
    {
        $score = $rawFeedback->rating;

        $tone = FeedbackItem::NEUTRAL;
        if ($rawFeedback->category == "promoter") {
            $tone = FeedbackItem::POSITIVE;
        } elseif ($rawFeedback->category == "passive") {
            $tone = FeedbackItem::PASSIVE;
        } elseif ($rawFeedback->category == "detractor") {
            $tone = FeedbackItem::NEGATIVE;
        }

        return $tone;
    }
}
