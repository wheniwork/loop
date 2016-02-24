<?php

namespace FeedbackTests;

use Equip\Adr\PayloadInterface;
use Wheniwork\Feedback\Domain\DoGeneric;

class DoGenericTest extends FeedbackTestBase
{
    protected function setUp()
    {
        $this->domain = new DoGeneric(
            $this->getMockHipChatService(),
            $this->getMockDatabaseService(),
            $this->getMockAuthorizer()
        );
    }

    public function testValidInput()
    {
        $payload = $this->payloadForInput([
            'body' => parent::ITEM_BODY,
            'source' => parent::ITEM_SOURCE,
            'title' => parent::ITEM_TITLE,
            'tone' => parent::ITEM_TONE,
        ]);

        $this->assertSame(PayloadInterface::STATUS_OK, $payload->getStatus());
        $this->assertArrayHasKey('new_feedback', $payload->getOutput());

        $feedbackItem = $payload->getOutput()['new_feedback'];
        $this->assertSame(parent::ITEM_BODY, $feedbackItem['body']);
        $this->assertSame(parent::ITEM_SOURCE, $feedbackItem['source']);
        $this->assertSame(parent::ITEM_TITLE, $feedbackItem['title']);
        $this->assertSame(parent::ITEM_TONE, $feedbackItem['tone']);
    }

    public function testMissingFields()
    {
        $payload = $this->payloadForInput([]);

        $this->assertSame(PayloadInterface::STATUS_BAD_REQUEST, $payload->getStatus());
        $this->assertArrayHasKey('error', $payload->getOutput());

        $errorMsg = $payload->getOutput()['error'];
        $this->assertContains('body', $errorMsg);
        $this->assertContains('source', $errorMsg);
    }

    public function testInvalidTone()
    {
        $payload = $this->payloadForInput([
            'body' => parent::ITEM_BODY,
            'source' => parent::ITEM_SOURCE,
            'tone' => 'badvalue',
        ]);

        $this->assertSame(PayloadInterface::STATUS_BAD_REQUEST, $payload->getStatus());
        $this->assertArrayHasKey('error', $payload->getOutput());
    }
}
