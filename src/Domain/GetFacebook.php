<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\FacebookService;

class GetFacebook extends FeedbackGetDomain
{
    protected function getRedisKey()
    {
        return "fb_last_time";
    }

    protected function getSourceName()
    {
        return "Facebook";
    }

    protected function getOutputKeyName()
    {
        return "new_comments";
    }

    protected function getFeedbackItems()
    {
        $replies = FacebookService::getReplyComments($this->getRedisValue());
        $replies = array_filter($replies, function($item) {
            return $this->isFeedbackComment($item);
        });

        $feedbackComments = [];
        foreach ($replies as $reply) {
            if (strtotime($reply['created_time']) <= $last_time) {
                continue;
            }

            $feedbackComments[] = FacebookService::getCommentParent($reply['id']);
        }

        return $feedbackComments;
    }

    protected function getValueForRedis($feedbackItem)
    {
        // This apparently might need to access a different
        // value - instead of getting the first item as usual,
        // this endpoint used to get the *last* item in the
        // response. Don't remember why. Test this!
        return strtotime($feedbackItem['created_time']);
    }

    protected function getFeedbackHTML($feedbackItem)
    {
        $body = $feedbackItem['message'];
        return "$body";
    }

    private function isFeedbackComment($comment)
    {
        $from_wheniwork = $comment['from']['id'] == $_ENV['FB_PAGE_ID'];
        $tagged_feedback = $this->isTaggedFeedback($comment['message']);
        return $from_wheniwork && $tagged_feedback;
    }
}
