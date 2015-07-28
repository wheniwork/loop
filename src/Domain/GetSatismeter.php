<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\SatismeterService;

class GetSatismeter extends FeedbackGetDomain
{
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
        $responses = SatismeterService::getResponses($this->getRedisValue());
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
        return "<strong>$score/10.</strong> $body";
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
