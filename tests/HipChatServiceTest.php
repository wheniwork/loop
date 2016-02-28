<?php

namespace FeedbackTests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use Wheniwork\Feedback\Formatter\HipChatFormatter;
use Wheniwork\Feedback\Service\HipChatService;

class HipChatServiceTest extends FeedbackTestBase
{
    /**
     * @var HipChatFormatter
     */
    private $formatter;

    /**
     * @var HipChatService
     */
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
            ->getMockBuilder(HttpClient::class)
            ->getMock();
        $mockHttpClient
            ->method('send')
            ->will($this->returnArgument(0));

        $this->formatter = new HipChatFormatter;

        return new HipChatService(
            $mockHttpClient,
            self::HIPCHAT_KEY,
            self::HIPCHAT_ROOM,
            $this->formatter
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

    public function testGetHipChatRequest()
    {
        $request = $this->service->getHipChatRequest('', ['key' => 'value']);

        $this->assertSame('POST', $request->getMethod());
        $this->assertContains('https://api.hipchat.com/v2', (string)$request->getUri());

        $this->assertArrayHasKey('Authorization', $request->getHeaders());
        $this->assertSame('Bearer ' . self::HIPCHAT_KEY, $request->getHeader('Authorization')[0]);

        $body = $request->getBody()->getContents();
        $parsedBody = json_decode($body);
        $this->assertInternalType('object', $parsedBody);
        $this->assertObjectHasAttribute('key', $parsedBody);
        $this->assertSame('value', $parsedBody->key);
    }

    public function testPostMessage()
    {
        $request = $this->service->postMessage(
            self::HIPCHAT_MESSAGE_CONTENT,
            self::HIPCHAT_MESSAGE_COLOR
        );

        $parsedBody = json_decode($this->getRequestBody($request), true);

        $this->assertSame(self::HIPCHAT_MESSAGE_CONTENT, $parsedBody['message']);
        $this->assertSame(self::HIPCHAT_MESSAGE_COLOR, $parsedBody['color']);
    }

    public function testPostFeedback()
    {
        $item = $this->getFeedbackItem();

        $request = $this->service->postFeedback($item);
        $parsedBody = json_decode($this->getRequestBody($request), true);

        $formattedItem = $this->formatter->format($item);
        $itemColor = $this->formatter->getColor($item);
        $this->assertSame($formattedItem, $parsedBody['message']);
        $this->assertSame($itemColor, $parsedBody['color']);
    }
}
