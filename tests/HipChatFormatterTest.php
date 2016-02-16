<?php

namespace FeedbackTests;

use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\Formatter\HipChatFormatter;
use Wheniwork\Feedback\Service\HipChatService;

class HipChatFormatterTest extends FeedbackTestBase
{
    private $formatter;

    protected function setUp()
    {
        $this->formatter = new HipChatFormatter;
    }

    private function assertItemGetsColor($expectedColor, $item)
    {
        $actualColor = $this->formatter->getColor($item);
        $this->assertSame($expectedColor, $actualColor);
    }

    public function testFormat()
    {
        $item = $this->getFeedbackItem();
        $result = $this->formatter->format($item);

        $this->assertContains($item->body, $result);
    }

    public function testGetColor()
    {
        $item = $this->getFeedbackItem();

        $this->assertItemGetsColor(HipChatService::GRAY, $item);

        $positiveItem = $item->withData(['tone' => FeedbackItem::POSITIVE]);
        $this->assertItemGetsColor(HipChatService::GREEN, $positiveItem);

        $passiveItem = $item->withData(['tone' => FeedbackItem::PASSIVE]);
        $this->assertItemGetsColor(HipChatService::YELLOW, $passiveItem);

        $negativeItem = $item->withData(['tone' => FeedbackItem::NEGATIVE]);
        $this->assertItemGetsColor(HipChatService::RED, $negativeItem);

        $neutralItem = $item->withData(['tone' => FeedbackItem::NEUTRAL]);
        $this->assertItemGetsColor(HipChatService::GRAY, $neutralItem);
    }
}
