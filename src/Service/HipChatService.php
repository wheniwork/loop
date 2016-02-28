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

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $room;

    /**
     * @var HipChatFormatter
     */
    private $formatter;

    public function __construct(HttpClient $httpClient, $key, $room, HipChatFormatter $formatter)
    {
        $this->httpClient = $httpClient;
        $this->key = $key;
        $this->room = $room;
        $this->formatter = $formatter;
    }

    /**
     * @param $endpoint
     * @param $params
     * @return Request
     */
    public function getHipChatRequest($endpoint, $params)
    {
        $url = 'https://api.hipchat.com/v2' . $endpoint;
        $postdata = json_encode($params);

        return new Request(
            'POST',
            $url,
            [
                'Authorization' => "Bearer $this->key",
                'Content-Type' => 'application/json'
            ],
            $postdata
        );
    }

    /**
     * @param $content
     * @param string $color
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function postMessage($content, $color = self::GRAY)
    {
        $endpoint = "/room/$this->room/notification";
        $params = [
            'message' => $content,
            'color' => $color,
            'notify' => true
        ];
        $request = $this->getHipChatRequest($endpoint, $params);

        return $this->httpClient->send($request);
    }

    /**
     * Formats and posts a FeedbackItem to the service's HipChat room.
     *
     * @param FeedbackItem $feedbackItem
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function postFeedback(FeedbackItem $feedbackItem)
    {
        $content = $this->formatter->format($feedbackItem);
        $color = $this->formatter->getColor($feedbackItem);
        return $this->postMessage($content, $color);
    }
}
