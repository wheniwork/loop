<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;
use Wheniwork\Feedback\Service\TwitterService;

class GetTwitter extends FeedbackGetDomain
{
    private $twitter;

    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database,
        RedisClient $redis,
        TwitterService $twitter
    ) {
        parent::__construct($hipchat, $database, $redis);
        $this->twitter = $twitter;
    }

    protected function getRedisKey()
    {
        return "twitter_last_id";
    }

    protected function getOutputKeyName()
    {
        return "new_tweets";
    }

    protected function getRawFeedbacks()
    {
        $tweets = $this->twitter->getTweetsSince($this->getRedisValue());
        
        $feedbackTweets = [];
        foreach ($tweets as $tweet) {
            $is_reply = !empty($tweet->in_reply_to_status_id);
            $tagged_feedback = $this->isTaggedFeedback($tweet->text);

            if ($is_reply && $tagged_feedback) {
                $originalTweet = $this->twitter->getTweet($tweet->in_reply_to_status_id);
                $is_new = strcmp($originalTweet->id_str, $this->getRedisValue()) > 0;
                if ($is_new) {
                    $feedbackTweets[] = $originalTweet;
                }
            }
        }
        
        return $feedbackTweets;
    }

    protected function getValueForRedis($feedbackItem)
    {
        return $feedbackItem->id_str;
    }

    protected function createFeedbackItem($rawFeedback)
    {
        return (new FeedbackItem)->withData([
            'body' => $rawFeedback->text,
            'source' => "Twitter",
            'link' => $this->twitter->getTweetURL($rawFeedback)
        ]);
    }
}
