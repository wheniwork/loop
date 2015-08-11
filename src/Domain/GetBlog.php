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
        $comments = $this->blog->getPublishedComments($this->getRedisValue(), true);
        $feedbackComments = [];
        foreach ($comments as $comment) {
            $is_reply = $comment['parent'] != "0";
            $tagged_feedback = $this->isTaggedFeedback($comment['content']);

            if ($is_reply && $tagged_feedback) {
                $parentComment = $this->blog->getComment($comment['parent']);
                $isNew = $parentComment['date_created_gmt']->timestamp > $this->getRedisValue();
                if ($isNew) {
                    $feedbackComments[] = $parentComment;
                }
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
}
