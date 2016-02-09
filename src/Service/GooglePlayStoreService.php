<?php
namespace Wheniwork\Feedback\Service;

use Goutte\Client as GoutteClient;

class GooglePlayStoreService
{
    private $client;
    private $app_id;

    public function __construct(GoutteClient $client, $app_id)
    {
        $this->client = $client;
        $this->app_id = $app_id;
    }

    public function getReviews($after_time)
    {
        $endpoint = "https://play.google.com/store/apps/details?id=$this->app_id";
        $crawler = $this->client->request('GET', $endpoint);

        $reviews = [];

        $crawler->filter('.single-review')->each(function ($node) use (&$reviews, $crawler, $after_time) {
            $review = [];

            $review['date'] = $node->filter('.review-date')->text();
            $review['timestamp'] = strtotime($review['date']);

            if ($review['timestamp'] <= $after_time) {
                return;
            }

            $review['author'] = $node->filter('.author-name a')->text();

            $rating = $node->filter('.tiny-star')->attr('aria-label');
            $rating = preg_replace('/.*Rated /', '', $rating);
            $rating = preg_replace('/ stars out of five stars.*/', '', $rating);
            $review['rating'] = intval($rating);

            $review['title'] = $node->filter('.review-title')->text();

            $body = $node->filter('.review-body')->html();
            $body = preg_replace('/<span.*?span>/s', '', $body);
            $body = preg_replace('/<div.*?div>/s', '', $body);
            $review['body'] = trim($body);

            $reviews[] = $review;
        });

        usort($reviews, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $reviews;
    }
}
