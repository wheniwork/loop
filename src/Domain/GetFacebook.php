<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\Service\FacebookService;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;

class GetFacebook extends FeedbackGetDomain
{
    private $facebook;

    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database,
        RedisClient $redis,
        FacebookService $facebook
    ) {
        parent::__construct($hipchat, $database, $redis);
        $this->facebook = $facebook;
    }

    protected function getRedisKey()
    {
        return 'fb_last_time';
    }

    protected function getOutputKeyName()
    {
        return 'new_comments';
    }

    protected function getRawFeedbacks()
    {
        $replies = $this->facebook->getReplyComments($this->getRedisValue());
        $replies = array_filter($replies, function ($item) {
            return $this->isFeedbackComment($item);
        });

        $feedbackComments = [];
        foreach ($replies as $reply) {
            if (strtotime($reply['created_time']) <= $last_time) {
                continue;
            }

            $feedbackComments[] = $this->facebook->getCommentParent($reply['id']);
        }

        return $feedbackComments;
    }

    protected function getValueForRedis($feedbackItem)
    {
        return strtotime($feedbackItem['created_time']);
    }

    protected function createFeedbackItem($rawFeedback)
    {
        return (new FeedbackItem)->withData([
            'body' => $feedbackItem['message'],
            'source' => 'Facebook'
        ]);
    }

    private function isFeedbackComment($comment)
    {
        $from_wheniwork = $comment['from']['id'] == $this->facebook->getPageId();
        $tagged_feedback = $this->isTaggedFeedback($comment['message']);
        return $from_wheniwork && $tagged_feedback;
    }
}
