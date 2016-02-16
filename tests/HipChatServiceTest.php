<?php

namespace FeedbackTests;

use FeedbackTests\FeedbackTestBase;
use GuzzleHttp\Psr7\Request;
use Wheniwork\Feedback\Formatter\HipChatFormatter;
use Wheniwork\Feedback\Service\HipChatService;

class HipChatServiceTest extends FeedbackTestBase
{
    private $service;

    const HIPCHAT_KEY = 'test_key';
    const HIPCHAT_ROOM = 'test_room';
    const HIPCHAT_MESSAGE_CONTENT = 'test_content';
    const HIPCHAT_MESSAGE_COLOR = HipChatService::GREEN;

    protected function setUp()
    {
        $this->service = $this->makeHipChatService();
    }

    private function makeHipChatService()
    {
        $mockHttpClient = $this
            ->getMockBuilder('GuzzleHttp\Client')
            ->getMock();
        $mockHttpClient
            ->method('send')
            ->will($this->returnArgument(0));

        return new HipChatService(
            $mockHttpClient,
            self::HIPCHAT_KEY,
            self::HIPCHAT_ROOM,
            new HipChatFormatter
        );
    }

    private function getPropertyValue($name, $object)
    {
        $class = new \ReflectionClass(get_class($object));
        $prop = $class->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    private function getRequestHeaders(Request $request)
    {
        return $this->getPropertyValue('headers', $request);
    }

    private function getRequestBody(Request $request)
    {
        $streamWrapper = $this->getPropertyValue('stream', $request);
        $stream = $this->getPropertyValue('stream', $streamWrapper);
        return stream_get_contents($stream);
    }

    public function testPostMessage()
    {
        $request = $this->service->postMessage(
            self::HIPCHAT_MESSAGE_CONTENT,
            self::HIPCHAT_MESSAGE_COLOR
        );

        $headers = $this->getRequestHeaders($request);
        $body = json_decode($this->getRequestBody($request), true);

        $this->assertContains(self::HIPCHAT_KEY, $headers['authorization'][0]);
        $this->assertSame(self::HIPCHAT_MESSAGE_CONTENT, $body['message']);
        $this->assertSame(self::HIPCHAT_MESSAGE_COLOR, $body['color']);
    }

    public function testPostFeedback()
    {
        $item = $this->getFeedbackItem();

        $request = $this->service->postFeedback($item);

        // TODO: What to actually test here?
    }
}
