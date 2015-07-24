<?php
namespace Wheniwork\Feedback\Service;

class AppStoreService
{
    public static function getReviews($app_id, $after_id)
    {
        $endpoint = "https://itunes.apple.com/rss/customerreviews/id=$app_id/sortBy=mostRecent/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($data, true)['feed'];

        $reviews = [];
        foreach ($response['entry'] as $entry) {
            if (empty($entry['author'])) {
                continue;
            }
            if ($entry['id']['label'] <= $after_id) {
                break;
            }

            $review = [];

            $review['id'] = intval($entry['id']['label']);
            $review['author'] = $entry['author']['name']['label'];
            $review['version'] = $entry['im:version']['label'];
            $review['rating'] = intval($entry['im:rating']['label']);
            $review['title'] = $entry['title']['label'];
            $review['content'] = $entry['content']['label'];

            $reviews[] = $review;
        }

        return $reviews;
    }
}
