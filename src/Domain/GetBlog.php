<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\BlogService;

class GetBlog extends FeedbackDomain
{
    const WP_REDIS_KEY = "wp_last_time";

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            // Initialize Redis key if necessary
            if (empty($this->getLastCommentTime())) {
                $this->saveLastCommentTime(0);
            }

            // Get new comments since we last checked
            $comments = BlogService::getPublishedComments(50, $this->getLastCommentTime());

            // Process new comments
            $output = ['new_comments' => []];
            foreach ($comments as $comment) {
                $is_reply = $comment['parent'] != "0";
                $from_feedback_user = in_array($comment['user_id'], $this->getFeedbackUsers());
                $tagged_feedback = $this->isTaggedFeedback($comment['content']);

                if ($is_reply && $from_feedback_user && $tagged_feedback) {
                    $parent_comment = BlogService::getComment($comment['parent']);

                    $this->createFeedback($parent_comment['content'], "the blog");
                    array_push($output['new_comments'], $parent_comment);
                }
            }

            // Set the time of the latest comment in Redis
            if (count($comments) > 0) {
                $this->saveLastCommentTime(reset($comments)['date_created_gmt']->timestamp);
            }
            
            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput($output);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
        }

        return $payload;
    }

    private function getFeedbackUsers() {
        return preg_split('/\s*,\s*/', $_ENV['WP_FEEDBACK_USERS']);
    }

    private function getLastCommentTime() {
        return $this->redis->get(self::WP_REDIS_KEY);
    }

    private function saveLastCommentTime($time) {
        $this->redis->set(self::WP_REDIS_KEY, $time);
    }
}
