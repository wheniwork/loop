<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\AppStoreService;

class GetAppStore extends FeedbackGetDomain
{
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
        return AppStoreService::getReviews($_ENV['WIW_IOS_APP_ID'], $this->getRedisValue());
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
