<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\Service\BlogService;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;

class GetBlog extends FeedbackGetDomain
{
    private $blog;

    public function __construct(
        HipChatService $hipchat,
        GithubService $github,
        RedisClient $redis,
        BlogService $blog
    ) {
        parent::__construct($hipchat, $github, $redis);
        $this->blog = $blog;
    }

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
        $comments = $this->blog->getPublishedComments(50, $this->getRedisValue());
        $feedbackComments = [];
        foreach ($comments as $comment) {
            $is_reply = $comment['parent'] != "0";
            $from_feedback_user = in_array($comment['user_id'], $this->getFeedbackUsers());
            $tagged_feedback = $this->isTaggedFeedback($comment['content']);

            if ($is_reply && $from_feedback_user && $tagged_feedback) {
                $feedbackComments[] = $this->blog->getComment($comment['parent']);
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
