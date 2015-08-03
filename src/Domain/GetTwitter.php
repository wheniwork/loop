<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;
use Wheniwork\Feedback\Service\TwitterService;

class GetTwitter extends FeedbackGetDomain
{
    private $twitter;

    public function __construct(
        HipChatService $hipchat,
        GithubService $github,
        RedisClient $redis,
        TwitterService $twitter
    ) {
        parent::__construct($hipchat, $github, $redis);
        $this->twitter = $twitter;
    }

    protected function getRedisKey()
    {
        return "twitter_last_id";
    }

    protected function getSourceName()
    {
        return "Twitter";
    }

    protected function getOutputKeyName()
    {
        return "new_tweets";
    }

    protected function getFeedbackItems()
    {
        $tweets = $this->twitter->getTweetsSince($this->getRedisValue());
        
        $feedbackTweets = [];
        foreach ($tweets as $tweet) {
            $is_reply = !empty($tweet->in_reply_to_status_id);
            $tagged_feedback = $this->isTaggedFeedback($tweet->text);

            if ($is_reply && $tagged_feedback) {
                $feedbackTweets[] = $this->getTweet($tweet->in_reply_to_status_id);
            }
        }
        
        return $feedbackTweets;
    }

    protected function getValueForRedis($feedbackItem)
    {
        return $feedbackItem->id;
    }

    protected function getFeedbackHTML($feedbackItem)
    {
        $body = $feedbackItem->text;
        $url = $this->getTweetURL($feedbackItem);
        return "$body<br><br><a href=\"$url\">$url</a>";
    }
}
