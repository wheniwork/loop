<?php

namespace FeedbackTests;

use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\FeedbackRating;

class FeedbackTestBase extends \PHPUnit_Framework_TestCase
{
    const ITEM_BODY = "test_body";
    const ITEM_SOURCE = "test_source";
    const ITEM_LINK = "http://www.example.com/testlink";
    const ITEM_TITLE = "test_title";
    const ITEM_RATING = 5;
    const ITEM_MAX_RATING = 5;
    const ITEM_SENDER = "test_sender";
    const ITEM_TONE = FeedbackItem::NEUTRAL;

    protected $domain;

    protected function payloadForInput(array $input)
    {
        return call_user_func($this->domain, $input);
    }

    public function getMockHttpClient()
    {
        $mockHttpClient = $this
            ->getMockBuilder('GuzzleHttp\Client')
            ->getMock();
        $mockHttpClient
            ->method('send')
            ->will($this->returnArgument(0));

        return $mockHttpClient;
    }

    public function getMockHipChatService()
    {
        return $this
            ->getMockBuilder('Wheniwork\Feedback\Service\HipChatService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getMockHipChatFormatter()
    {
        return $this
            ->getMockBuilder('Wheniwork\Feedback\Formatter\HipChatFormatter')
            ->getMock();
    }

    public function getMockDatabaseService()
    {
        return $this
            ->getMockBuilder('Wheniwork\Feedback\Service\DatabaseService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getMockAuthorizer()
    {
        return $this
            ->getMockBuilder('Wheniwork\Feedback\Service\Authorizer')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return FeedbackItem
     */
    public function getFeedbackItem()
    {
        return (new FeedbackItem)->withData([
            'body' => self::ITEM_BODY,
            'source' => self::ITEM_SOURCE,
            'link' => self::ITEM_LINK,
            'title' => self::ITEM_TITLE,
            'rating' => new FeedbackRating(
                self::ITEM_RATING,
                self::ITEM_MAX_RATING
            ),
            'sender' => self::ITEM_SENDER,
            'tone' => self::ITEM_TONE,
        ]);
    }
}
