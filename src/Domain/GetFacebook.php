<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\FacebookService;

class GetFacebook extends FeedbackDomain
{
    const FB_REDIS_KEY = 'fb_last_time';

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            // Initialize Redis key if necessary
            if (empty($this->getLastPostTime())) {
                $this->saveLastPostTime(1);
            }
            $last_time = $this->getLastPostTime();

            // Get new posts since we last checked
            $replies = FacebookService::getReplyComments($this->getLastPostTime());
            $payload->setOutput($replies);
            $replies = array_filter($replies, function($item) {
                return $this->isFeedbackComment($item);
            });

            // Process new feedback comments
            $output = ['new_comments' => []];
            foreach ($replies as $reply) {
                if (strtotime($reply['created_time']) > $last_time) {
                    $parent = FacebookService::getCommentParent($reply['id']);
                    $this->createFeedback($parent['message']);
                    array_push($output['new_comments'], $parent);
                }
            }

            // Set the time of the latest reply comment in Redis
            if (count($replies) > 0) {
                $this->saveLastPostTime(strtotime(end($replies)['created_time']));
            }

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput($output);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
        }

        return $payload;
    }

    private function getLastPostTime() {
        return $this->redis->get(self::FB_REDIS_KEY);
    }

    private function saveLastPostTime($time) {
        $this->redis->set(self::FB_REDIS_KEY, $time);
    }

    private function isFeedbackComment($comment) {
        $from_wheniwork = $comment['from']['id'] == $_ENV['FB_PAGE_ID'];
        $tagged_feedback = strpos($comment['message'], "#feedback") !== FALSE;
        return $from_wheniwork && $tagged_feedback;
    }
}
