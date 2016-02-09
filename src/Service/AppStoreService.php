<?php
namespace Wheniwork\Feedback\Service;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;

class AppStoreService
{
    private $httpClient;
    private $app_id;

    public function __construct(HttpClient $httpClient, $app_id)
    {
        $this->httpClient = $httpClient;
        $this->app_id = $app_id;
    }

    public function getReviews($after_id)
    {
        $endpoint = "https://itunes.apple.com/rss/customerreviews/id=$this->app_id/sortBy=mostRecent/json";

        $request = new Request('GET', $endpoint);

        $data = $this->httpClient->send($request)->getBody();
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
