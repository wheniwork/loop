<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\BlogService;

class GetBlog extends FeedbackGetDomain
{
    protected function getRedisKey()
    {
        return "wp_last_time";
    }

    protected function getSourceName()
    {
        return "the blog";
    }

    protected function getOutputKeyName()
    {
        return "new_comments";
    }

    protected function getFeedbackItems()
    {
        $comments = BlogService::getPublishedComments(50, $this->getRedisValue());
        $feedbackComments = [];
        foreach ($comments as $comment) {
            $is_reply = $comment['parent'] != "0";
            $from_feedback_user = in_array($comment['user_id'], $this->getFeedbackUsers());
            $tagged_feedback = $this->isTaggedFeedback($comment['content']);

            if ($is_reply && $from_feedback_user && $tagged_feedback) {
                $feedbackComments[] = BlogService::getComment($comment['parent']);
            }
        }
        return $feedbackComments;
    }

    protected function getValueForRedis($feedbackItem)
    {
        return $feedbackItem['date_created_gmt']->timestamp;
    }

    protected function getFeedbackHTML($feedbackItem)
    {
        $body = $feedbackItem['content'];
        $url = $feedbackItem['link'];
        return "$body<br><br><a href=\"$url\">$url</a>";
    }

    private function getFeedbackUsers()
    {
        return preg_split('/\s*,\s*/', $_ENV['WP_FEEDBACK_USERS']);
    }
}
