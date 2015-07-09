<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\TwitterService;
use TwitterAPIExchange;

class GetTwitter extends FeedbackDomain
{
    const TWITTER_REDIS_KEY = "twitter_last_id";

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            // Initialize Redis key if necessary
            if (empty($this->getLastTweetID())) {
                $this->saveLastTweetID(1);
            }

            // Get new tweets since we last checked
            $last_id = $this->getLastTweetID();
            $tweets = $this->getTweetsSince($last_id);

            // Set the id of the latest tweet in Redis
            if (count($tweets) > 0) {
                $this->saveLastTweetID(reset($tweets)->id);
            }

            // Process new feedback tweets
            $output = ['new_tweets' => []];
            foreach ($tweets as $tweet) {
                $is_reply = !empty($tweet->in_reply_to_status_id);
                $tagged_feedback = strpos($tweet->text, '#feedback') !== FALSE;

                if ($is_reply && $tagged_feedback) {
                    $replied_tweet = $this->getTweet($tweet->in_reply_to_status_id);

                    $this->createFeedback($replied_tweet->text);
                    array_push($output['new_tweets'], $replied_tweet);
                }
            }

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput($output);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
        }
        
        return $payload;
    }

    private function getTweetsSince($last_id) {
        return TwitterService::get('https://api.twitter.com/1.1/statuses/user_timeline.json', [
            'screen_name' => $_ENV['TWITTER_WATCH_NAME'],
            'since_id' => $last_id
        ]);
    }

    private function getTweet($id) {
        return TwitterService::get('https://api.twitter.com/1.1/statuses/show.json', [
            'id' => $id
        ]);
    }

    private function getLastTweetID() {
        return $this->redis->get(self::TWITTER_REDIS_KEY);
    }

    private function saveLastTweetID($id) {
        $this->redis->set(self::TWITTER_REDIS_KEY, $id);
    }
}
