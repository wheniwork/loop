<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\Service\AppStoreService;

class GetAppStore extends FeedbackDomain
{
    const APP_STORE_REDIS_KEY = "app_store_last_id";

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            // Initialize Redis key if necessary
            if (empty($this->getLastReviewID())) {
                $this->setLastReviewID(1);
            }

            // Get new reviews since we last looked
            $reviews = AppStoreService::getReviews($_ENV['WIW_IOS_APP_ID'], $this->getLastReviewID());

            // Set the id of the latest review in Redis
            if (count($reviews) > 0) {
                $this->setLastReviewID(reset($reviews)['id']);
            }

            // Process new reviews
            $output = ['new_reviews' => []];
            foreach ($reviews as $review) {
                $body = $review['content'];
                $title = $review['title'];
                $score = $review['rating'];
                $tone = self::NEUTRAL;
                if ($score >= 4) {
                    $tone = self::POSITIVE;
                } else if ($score == 3) {
                    $tone = self::PASSIVE;
                } else if ($score <= 2) {
                    $tone = self::NEGATIVE;
                }

                $feedback_html = "<strong>$title ($score/5)</strong><br>$body";

                $this->createFeedback($feedback_html, "the iTunes App Store", $tone);
                array_push($output['new_reviews'], $review);
            }

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput($output);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
        }
        
        return $payload;
    }

    private function getLastReviewID() {
        return $this->redis->get(self::APP_STORE_REDIS_KEY);
    }

    private function setLastReviewID($id) {
        $this->redis->set(self::APP_STORE_REDIS_KEY, $id);
    }
}
