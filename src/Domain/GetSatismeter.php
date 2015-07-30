<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;
use Wheniwork\Feedback\Service\SatismeterService;

class GetSatismeter extends FeedbackGetDomain
{
    private $satismeter;
    
    public function __construct(
        HipChatService $hipchat,
        GithubService $github,
        RedisClient $redis,
        SatismeterService $satismeter
    ) {
        parent::__construct($hipchat, $github, $redis);
        $this->satismeter = $satismeter;
    }

    protected function getRedisKey()
    {
        return "satismeter_last_time";
    }

    protected function getSourceName()
    {
        return "Satismeter";
    }

    protected function getOutputKeyName()
    {
        return "new_responses";
    }

    protected function getFeedbackItems()
    {
        $responses = $this->satismeter->getResponses($this->getRedisValue());
        $feedbackResponses = [];
        foreach ($responses as $response) {
            if (empty($response->feedback)) {
                continue;
            }

            $feedbackResponses[] = $response;
        }

        return $feedbackResponses;
    }

    protected function initRedis()
    {
        $startOfDay = strtotime("midnight");
        $this->setRedisValue($startOfDay);
    }

    protected function getValueForRedis($feedbackItem)
    {
        return strtotime($feedbackItem->created) + 1;
    }

    protected function getFeedbackHTML($feedbackItem)
    {
        $score = $feedbackItem->rating;
        $body = $feedbackItem->feedback;
        $email = $feedbackItem->user->email;
        return "<strong>$score/10.</strong> $body <i>(From $email)</i>";
    }

    protected function getTone($feedbackItem)
    {
        $score = $feedbackItem->rating;

        $tone = self::NEUTRAL;
        if ($feedbackItem->category == "promoter") {
            $tone = self::POSITIVE;
        } else if ($feedbackItem->category == "passive") {
            $tone = self::PASSIVE;
        } else if ($feedbackItem->category == "detractor") {
            $tone = self::NEGATIVE;
        }

        return $tone;
    }
}
