<?php
namespace Wheniwork\Feedback\Service;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\Formatter\HipChatFormatter;

class HipChatService
{
    const GRAY = 'gray';
    const GREEN = 'green';
    const YELLOW = 'yellow';
    const RED = 'red';
    const PURPLE = 'purple';
    const RANDOM = 'random';

    private $httpClient;
    private $key;
    private $room;
    private $formatter;

    public function __construct(HttpClient $httpClient, $key, $room, HipChatFormatter $formatter)
    {
        $this->httpClient = $httpClient;
        $this->key = $key;
        $this->room = $room;
        $this->formatter = $formatter;
    }

    private function post($endpoint, $params)
    {
        $url = 'https://api.hipchat.com/v2' . $endpoint;
        $postdata = json_encode($params);
        $request = new Request(
            'POST',
            $url,
            [
                'Authorization' => "Bearer $this->key",
                'Content-Type' => 'application/json'
            ],
            $postdata
        );

        $response = $this->httpClient->send($request);

        return $response;
    }

    public function postMessage($content, $color = self::GRAY)
    {
        $this->post("/room/$this->room/notification", [
            'message' => $content,
            'color' => $color,
            'notify' => true
        ]);
    }

    public function postFeedback(FeedbackItem $feedbackItem)
    {
        $content = $this->formatter->format($feedbackItem);
        $color = $this->formatter->getColor($feedbackItem);
        $this->postMessage($content, $color);
    }
}
